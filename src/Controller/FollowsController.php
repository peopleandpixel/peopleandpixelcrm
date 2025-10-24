<?php

declare(strict_types=1);

namespace App\Controller;

use App\Util\Csrf;
use App\Util\Flash;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;

final class FollowsController
{
    public function __construct(
        private readonly object $followsStore,
        private readonly ?object $commentsStore = null,
        private readonly ?object $usersStore = null,
        private readonly ?\App\Service\EmailService $email = null,
        private readonly ?\App\Config $config = null,
    ) {}

    #[NoReturn]
    public function toggle(): void
    {
        $t = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($t) ? $t : null)) { http_response_code(400); render('errors/400'); return; }
        $entity = isset($_POST['entity']) ? strtolower(trim((string)$_POST['entity'])) : '';
        $entityId = (int)($_POST['entity_id'] ?? 0);
        if (!in_array($entity, ['contacts','tasks','projects','deals'], true) || $entityId <= 0) { Flash::error(__('Invalid target.')); redirect('/'); }
        // Require auth
        $user = \App\Util\Auth::user();
        if (!$user) { Flash::error(__('Please log in.')); redirect(url('/' . $entity . '/view', ['id' => $entityId])); }
        $login = strtolower((string)($user['login'] ?? ''));
        if ($login === '') { Flash::error(__('Invalid user.')); redirect(url('/' . $entity . '/view', ['id' => $entityId])); }
        // Permission: need to be able to view the record
        if (!\App\Util\Permission::can($entity, 'view')) { Flash::error(__('Not allowed.')); redirect(url('/' . $entity . '/view', ['id' => $entityId])); }
        // Toggle
        $existingId = null;
        foreach ($this->followsStore->all() as $f) {
            if (($f['entity'] ?? '') === $entity && (int)($f['entity_id'] ?? 0) === $entityId && strtolower((string)($f['user_login'] ?? '')) === $login) {
                $existingId = (int)($f['id'] ?? 0);
                break;
            }
        }
        if ($existingId) {
            $this->followsStore->delete($existingId);
            Flash::success(__('Unfollowed.'));
        } else {
            $this->followsStore->add([
                'entity' => $entity,
                'entity_id' => $entityId,
                'user_login' => $login,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            Flash::success(__('Following.'));
        }
        redirect(url('/' . $entity . '/view', ['id' => $entityId]));
    }

    #[NoReturn]
    public function digest(): void
    {
        $t = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($t) ? $t : null)) { http_response_code(400); render('errors/400'); return; }
        // Admin only
        if (!\App\Util\Auth::isAdmin()) { http_response_code(403); render('errors/405', ['path' => '/admin/follows/digest', 'allowed' => ['POST']]); return; }
        if (!$this->email || !$this->config || !$this->commentsStore || !$this->usersStore) { Flash::error(__('Email service not configured.')); redirect('/'); }
        $notify = $this->config->getEnv('NOTIFY_FOLLOWS');
        $notifyEnabled = in_array(strtolower((string)$notify), ['1','true','yes','on'], true);
        if (!$notifyEnabled) { Flash::error(__('Follow digests are disabled.')); redirect('/'); }
        $since = time() - 86400; // last 24h
        // Build comments index by (entity,entity_id)
        $recentByKey = [];
        foreach ($this->commentsStore->all() as $c) {
            $created = strtotime((string)($c['created_at'] ?? '')) ?: 0;
            if ($created < $since) { continue; }
            $entity = (string)($c['entity'] ?? '');
            $eid = (int)($c['entity_id'] ?? 0);
            if ($entity === '' || $eid <= 0) { continue; }
            $key = $entity . ':' . $eid;
            $recentByKey[$key] = $recentByKey[$key] ?? [];
            $recentByKey[$key][] = $c;
        }
        if (empty($recentByKey)) { Flash::success(__('No recent activity to send.')); redirect('/'); }
        // Map follows per user
        $followsByUser = [];
        foreach ($this->followsStore->all() as $f) {
            $login = strtolower((string)($f['user_login'] ?? ''));
            $entity = (string)($f['entity'] ?? '');
            $eid = (int)($f['entity_id'] ?? 0);
            if ($login === '' || $entity === '' || $eid <= 0) { continue; }
            $key = $entity . ':' . $eid;
            if (!isset($recentByKey[$key])) { continue; }
            $followsByUser[$login] = $followsByUser[$login] ?? [];
            $followsByUser[$login][$key] = true;
        }
        if (empty($followsByUser)) { Flash::success(__('No followers to notify.')); redirect('/'); }
        // Users index by login
        $users = $this->usersStore->all();
        $byLogin = [];
        foreach ($users as $u) { $byLogin[strtolower((string)($u['login'] ?? ''))] = $u; }
        $sent = 0;
        foreach ($followsByUser as $login => $keys) {
            $u = $byLogin[$login] ?? null;
            if (!$u) { continue; }
            $email = (string)($u['email'] ?? '');
            if ($email === '') { continue; }
            $lines = [];
            foreach (array_keys($keys) as $key) {
                $parts = explode(':', $key, 2);
                $entity = $parts[0]; $eid = (int)$parts[1];
                $title = ucfirst($entity) . ' #' . $eid;
                $url = url('/' . $entity . '/view', ['id' => $eid]);
                $lines[] = $title . ' — ' . $url;
                foreach ($recentByKey[$key] as $c) {
                    $author = (string)($c['created_by'] ?? '');
                    $msg = (string)($c['message'] ?? '');
                    $created = (string)($c['created_at'] ?? '');
                    $lines[] = '  - ' . $created . ' ' . $author . ': ' . $msg;
                }
                $lines[] = '';
            }
            if (empty($lines)) { continue; }
            $subject = __('Your followed items: daily digest');
            $body = implode("\n", $lines);
            try { $this->email->send($email, $subject, $body); $sent++; } catch (\Throwable) {}
        }
        Flash::success(sprintf(__('Digest sent to %d users.'), $sent));
        redirect('/');
    }
}
