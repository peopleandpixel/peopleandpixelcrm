<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\EmailService;
use App\Util\Flash;

final class EmailController
{
    public function __construct(
        private readonly EmailService $email,
        private readonly object $contactsStore,
        private readonly ?object $activitiesStore = null,
    ) {}

    /** Send an email to a contact and log activity. */
    public function sendToContact(): void
    {
        $contactId = (int)($_POST['contact_id'] ?? 0);
        $subject = trim((string)($_POST['subject'] ?? ''));
        $body = trim((string)($_POST['body'] ?? ''));
        $return = isset($_POST['return']) ? (string)$_POST['return'] : url('/contacts/view', ['id' => $contactId]);
        if ($contactId <= 0) { Flash::error(__('Invalid contact')); redirect('/contacts'); }
        $contact = $this->contactsStore->get($contactId);
        if (!$contact) { Flash::error(__('Contact not found')); redirect('/contacts'); }
        $to = (string)($contact['email'] ?? '');
        if ($to === '' && isset($contact['emails']) && is_array($contact['emails']) && count($contact['emails']) > 0) {
            $first = $contact['emails'][0];
            $to = is_array($first) ? (string)($first['value'] ?? '') : (string)$first;
        }
        if ($to === '') {
            Flash::error(__('Contact has no email address'));
            redirect($return);
        }
        if ($subject === '' || $body === '') {
            Flash::error(__('Subject and message are required'));
            redirect($return);
        }
        $res = $this->email->send($to, $subject, $body, null, null);
        if ($res['ok'] ?? false) {
            Flash::success(__('Email sent'));
            // Log activity
            if ($this->activitiesStore) {
                try {
                    $this->activitiesStore->add([
                        'contact_id' => $contactId,
                        'type' => 'email_out',
                        'title' => $subject,
                        'message' => $body,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Throwable $e) { /* ignore */ }
            }
        } else {
            $err = (string)($res['error'] ?? 'unknown');
            Flash::error(__('Email failed') . ': ' . $err);
        }
        redirect($return);
    }
}
