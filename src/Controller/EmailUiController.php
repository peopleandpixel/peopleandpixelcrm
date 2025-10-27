<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config;
use App\Service\ImapIngestService;
use App\Service\EmailService;
use App\Util\Flash;

final class EmailUiController
{
    public function __construct(
        private readonly Config $config,
        private readonly ImapIngestService $imap,
        private readonly EmailService $mailer,
        private readonly object $contactsStore,
        private readonly object $tasksStore,
        private readonly object $projectsStore,
    ) {}

    /** Inbox list sourced from data/emails_ingest.json */
    public function inbox(): void
    {
        $path = $this->config->jsonPath('emails_ingest.json');
        $items = [];
        if (is_file($path)) {
            $json = @file_get_contents($path);
            $arr = $json !== false ? json_decode($json, true) : null;
            if (is_array($arr)) { $items = $arr; }
        }
        // Sort by date desc if present
        usort($items, function(array $a, array $b) {
            $da = strtotime((string)($a['date'] ?? '')) ?: 0;
            $db = strtotime((string)($b['date'] ?? '')) ?: 0;
            return $db <=> $da;
        });
        render('email/inbox', [
            'items' => $items,
            'enabled' => $this->imap->isEnabled(),
        ]);
    }

    /** Manually trigger a quick IMAP ingest (unseen headers). */
    public function sync(): void
    {
        $res = $this->imap->ingestOnce(20);
        if ($res['ok'] ?? false) {
            Flash::success(__('Synced {n} emails', ['n' => (string)$res['ingested']]));
        } else {
            $err = (string)($res['error'] ?? 'unknown');
            Flash::error(__('Sync failed') . ': ' . $err);
        }
        redirect('/email');
    }

    /** View a single ingested item by UID (headers snapshot only). */
    public function view(): void
    {
        $uid = isset($_GET['uid']) ? (string)$_GET['uid'] : '';
        if ($uid === '') { redirect('/email'); }
        $item = $this->findIngestItem($uid);
        if (!$item) { Flash::error(__('Email not found')); redirect('/email'); }
        render('email/view', [
            'item' => $item,
        ]);
    }

    /** Add the sender as a Contact if not present. */
    public function addContact(): void
    {
        $email = isset($_POST['from']) ? (string)$_POST['from'] : '';
        $return = isset($_POST['return']) ? (string)$_POST['return'] : url('/email/view', ['uid' => (string)($_POST['uid'] ?? '')]);
        if ($email === '') { Flash::error(__('Missing email')); redirect($return); }
        // Check if exists
        try {
            $exists = false;
            foreach ($this->contactsStore->all() as $c) {
                $em = (string)($c['email'] ?? '');
                if ($em !== '' && strcasecmp($em, $email) === 0) { $exists = true; break; }
                if (!$exists && isset($c['emails']) && is_array($c['emails'])) {
                    foreach ($c['emails'] as $e) {
                        $val = is_array($e) ? (string)($e['value'] ?? '') : (string)$e;
                        if ($val !== '' && strcasecmp($val, $email) === 0) { $exists = true; break 2; }
                    }
                }
            }
            if ($exists) {
                Flash::success(__('Contact already exists'));
                redirect($return);
            }
            $name = $this->guessNameFromEmail($email);
            $this->contactsStore->add([
                'name' => $name,
                'email' => $email,
                'tags' => ['from-email'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            Flash::success(__('Contact created'));
        } catch (\Throwable $e) {
            Flash::error(__('Failed to create contact'));
        }
        redirect($return);
    }

    /** Create a Task from the email's subject/body (headers only available here). */
    public function createTask(): void
    {
        $subject = trim((string)($_POST['subject'] ?? ''));
        $from = (string)($_POST['from'] ?? '');
        $uid = (string)($_POST['uid'] ?? '');
        $contactId = 0;
        // Try link to contact by sender
        try {
            foreach ($this->contactsStore->all() as $c) {
                $em = (string)($c['email'] ?? '');
                if ($em !== '' && strcasecmp($em, $from) === 0) { $contactId = (int)$c['id']; break; }
                if (!$contactId && isset($c['emails']) && is_array($c['emails'])) {
                    foreach ($c['emails'] as $e) {
                        $val = is_array($e) ? (string)($e['value'] ?? '') : (string)$e;
                        if ($val !== '' && strcasecmp($val, $from) === 0) { $contactId = (int)$c['id']; break 2; }
                    }
                }
            }
            $title = $subject !== '' ? $subject : __('Follow up email from {email}', ['email' => $from]);
            $this->tasksStore->add([
                'project_id' => 0,
                'contact_id' => $contactId,
                'employee_id' => 0,
                'title' => $title,
                'notes' => 'From: ' . $from . "\nUID: " . $uid,
                'status' => 'todo',
                'tags' => ['from-email'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            Flash::success(__('Task created'));
        } catch (\Throwable $e) {
            Flash::error(__('Failed to create task'));
        }
        $return = isset($_POST['return']) ? (string)$_POST['return'] : url('/email/view', ['uid' => $uid]);
        redirect($return);
    }

    /** Create a Project prefilled from email subject. */
    public function createProject(): void
    {
        $subject = trim((string)($_POST['subject'] ?? ''));
        $from = (string)($_POST['from'] ?? '');
        $uid = (string)($_POST['uid'] ?? '');
        try {
            $name = $subject !== '' ? $subject : __('Project from email {email}', ['email' => $from]);
            $this->projectsStore->add([
                'name' => $name,
                'status' => 'active',
                'notes' => 'From: ' . $from . "\nUID: " . $uid,
                'tags' => ['from-email'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            Flash::success(__('Project created'));
        } catch (\Throwable $e) {
            Flash::error(__('Failed to create project'));
        }
        $return = isset($_POST['return']) ? (string)$_POST['return'] : url('/email/view', ['uid' => $uid]);
        redirect($return);
    }

    /** Compose and send a new email (simple). */
    public function composeForm(): void
    {
        render('email/compose', []);
    }

    public function send(): void
    {
        $to = trim((string)($_POST['to'] ?? ''));
        $subject = trim((string)($_POST['subject'] ?? ''));
        $body = trim((string)($_POST['body'] ?? ''));
        $return = isset($_POST['return']) ? (string)$_POST['return'] : '/email';
        if ($to === '' || $subject === '' || $body === '') {
            Flash::error(__('To, subject and body are required'));
            redirect($return);
        }
        $res = $this->mailer->send($to, $subject, $body, null, null);
        if ($res['ok'] ?? false) { Flash::success(__('Email sent')); }
        else { Flash::error(__('Email failed') . ': ' . (string)($res['error'] ?? 'unknown')); }
        redirect($return);
    }

    private function findIngestItem(string $uid): ?array
    {
        $path = $this->config->jsonPath('emails_ingest.json');
        if (!is_file($path)) return null;
        $json = @file_get_contents($path);
        $arr = $json !== false ? json_decode($json, true) : null;
        if (!is_array($arr)) return null;
        foreach ($arr as $it) {
            if ((string)($it['uid'] ?? '') === $uid) return $it;
        }
        return null;
    }

    private function guessNameFromEmail(string $email): string
    {
        $local = strstr($email, '@', true) ?: $email;
        $local = str_replace(['.', '_', '-'], ' ', $local);
        $local = ucwords(preg_replace('/\s+/', ' ', $local) ?: $local);
        return $local;
    }
}
