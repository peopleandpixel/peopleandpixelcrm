<?php

declare(strict_types=1);

namespace App\Controller;

use App\Util\Csrf;
use App\Util\Flash;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;

final class CommentsController
{
    public function __construct(
        private readonly object $commentsStore,
        private readonly object $usersStore,
        private readonly ?\App\Service\EmailService $email = null,
        private readonly ?\App\Config $config = null,
        private readonly ?\App\Service\AutomationService $automation = null,
    ) {}

    /**
     * Add a comment to an entity (contacts|tasks|projects|deals).
     */
    #[NoReturn]
    public function add(): void
    {
        $t = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($t) ? $t : null)) { http_response_code(400); render('errors/400'); return; }
        $entity = isset($_POST['entity']) ? strtolower(trim((string)$_POST['entity'])) : '';
        $entityId = (int)($_POST['entity_id'] ?? 0);
        $parentId = (int)($_POST['parent_id'] ?? 0);
        $message = trim((string)($_POST['message'] ?? ''));
        if (!in_array($entity, ['contacts','tasks','projects','deals'], true) || $entityId <= 0) { Flash::error(__('Invalid target for comment.')); redirect('/'); }
        if ($message === '') { Flash::error(__('Comment cannot be empty.')); redirect($this->backUrl($entity, $entityId)); }
        // Permission: require create on target entity
        if (!\App\Util\Permission::can($entity, 'create')) { Flash::error(__('Not allowed.')); redirect($this->backUrl($entity, $entityId)); }

        $user = \App\Util\Auth::user();
        $author = $user ? ((string)($user['login'] ?? ($user['fullname'] ?? ''))) : 'anonymous';
        $mentions = $this->parseMentions($message);
        $rec = [
            'entity' => $entity,
            'entity_id' => $entityId,
            'parent_id' => $parentId > 0 ? $parentId : null,
            'message' => $message,
            'mentions' => $mentions,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $author,
        ];
        $saved = $this->commentsStore->add($rec);
        $this->notifyMentions($entity, $entityId, $mentions, $message, $author);
        // Fire automation event
        $this->automation?->runForEvent('comment.added', [
            'entity' => $entity,
            'entity_id' => $entityId,
            'author' => $author,
            'message' => $message,
            'mentions' => $mentions,
            'comment_id' => (int)($saved['id'] ?? 0),
        ]);
        Flash::success(__('Comment added.'));
        redirect($this->backUrl($entity, $entityId));
    }

    #[NoReturn]
    public function delete(): void
    {
        $t = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($t) ? $t : null)) { http_response_code(400); render('errors/400'); return; }
        $id = (int)($_POST['id'] ?? 0);
        $entity = isset($_POST['entity']) ? strtolower(trim((string)$_POST['entity'])) : '';
        $entityId = (int)($_POST['entity_id'] ?? 0);
        if ($id <= 0) { redirect('/'); }
        // only admin or author can delete
        $item = $this->commentsStore->get($id) ?? null;
        if (!$item) { redirect($this->backUrl($entity, $entityId)); }
        $user = \App\Util\Auth::user();
        $isAdmin = \App\Util\Auth::isAdmin();
        $author = is_array($item) ? (string)($item['created_by'] ?? '') : '';
        $me = $user ? ((string)($user['login'] ?? ($user['fullname'] ?? ''))) : '';
        if (!$isAdmin && $author !== '' && $author !== $me) {
            Flash::error(__('Not allowed to delete this comment.'));
            redirect($this->backUrl($entity, $entityId));
        }
        $this->commentsStore->delete($id);
        Flash::success(__('Comment deleted.'));
        redirect($this->backUrl($entity, $entityId));
    }

    /**
     * @return array<int,string> user logins mentioned
     */
    private function parseMentions(string $message): array
    {
        $out = [];
        if (preg_match_all('/@([A-Za-z0-9_\.-]+)/', $message, $m)) {
            foreach ($m[1] as $login) {
                $login = strtolower($login);
                if ($login !== '' && !in_array($login, $out, true)) { $out[] = $login; }
            }
        }
        return $out;
    }

    private function notifyMentions(string $entity, int $entityId, array $mentions, string $message, string $author): void
    {
        if (!$this->email || !$this->config) { return; }
        $notify = $this->config->getEnv('NOTIFY_COMMENTS');
        $self = $this->config->getEnv('NOTIFY_SELF');
        $notifyEnabled = in_array(strtolower((string)$notify), ['1','true','yes','on'], true);
        $notifySelf = in_array(strtolower((string)$self), ['1','true','yes','on'], true);
        if (!$notifyEnabled || empty($mentions)) { return; }
        // Build index of users by login
        $users = $this->usersStore->all();
        $byLogin = [];
        foreach ($users as $u) { $byLogin[strtolower((string)($u['login'] ?? ''))] = $u; }
        $baseUrl = url('/' . $entity . '/view', ['id' => $entityId]);
        foreach ($mentions as $login) {
            $u = $byLogin[$login] ?? null;
            if (!$u) { continue; }
            $email = (string)($u['email'] ?? '');
            if ($email === '') { continue; }
            $target = (string)($u['login'] ?? ($u['fullname'] ?? ''));
            if (!$notifySelf && strtolower($target) === strtolower($author)) { continue; }
            $subject = '[' . ucfirst($entity) . ' #' . $entityId . '] ' . __('You were mentioned in a comment');
            $body = $author . ' ' . __('mentioned you:') . "\n\n" . $message . "\n\n" . __('Open:') . ' ' . $baseUrl;
            try { $this->email->send($email, $subject, $body); } catch (\Throwable $e) { /* ignore */ }
        }
    }

    private function backUrl(string $entity, int $id): string
    {
        return url('/' . $entity . '/view', ['id' => $id]);
    }
}
