<?php

declare(strict_types=1);

namespace App\Controller;

use App\Util\Csrf;
use App\Util\Flash;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;

final class BulkController
{
    public function __construct(
        private readonly object $contactsStore,
        private readonly object $dealsStore,
        private readonly object $projectsStore,
        private readonly ?\App\Service\AuditService $audit = null,
    ) {}

    /**
     * Undo last bulk delete for supported entities.
     * Restores records saved in session under _bulk_undo with 5-minute expiry.
     */
    #[NoReturn]
    public function undo(): void
    {
        $token = (string)($_POST['token'] ?? '');
        $entity = (string)($_POST['entity'] ?? '');
        $t = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($t) ? $t : null)) { http_response_code(400); render('errors/400'); return; }
        if ($token === '' || $entity === '') { Flash::error(__('Invalid undo request.')); redirect('/'); }
        if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
        $bucket = $_SESSION['_bulk_undo'] ?? [];
        if (!isset($bucket[$token])) { Flash::error(__('Undo token not found or expired.')); redirect('/'); }
        $payload = $bucket[$token];
        // Expire after 5 minutes
        if (!is_array($payload) || (time() - (int)($payload['at'] ?? 0)) > 300) {
            unset($_SESSION['_bulk_undo'][$token]);
            Flash::error(__('Undo token expired.'));
            redirect('/');
        }
        if (($payload['entity'] ?? '') !== $entity) {
            Flash::error(__('Entity mismatch for undo.'));
            redirect('/');
        }
        $records = $payload['records'] ?? [];
        if (!is_array($records) || empty($records)) { Flash::error(__('Nothing to restore.')); redirect('/'); }

        $store = $this->storeFor($entity);
        if (!$store) { Flash::error(__('Unsupported entity for undo.')); redirect('/'); }

        $restored = 0; $failed = 0;
        foreach ($records as $rec) {
            if (!is_array($rec)) { $failed++; continue; }
            // Ensure id is not conflicting; if existing, skip restore for that record
            $id = (int)($rec['id'] ?? 0);
            $exists = $id > 0 ? $store->get($id) : null;
            if ($exists) { $failed++; continue; }
            try {
                $store->add($rec);
                if ($this->audit) { $this->audit->record('restored', $entity, $rec['id'] ?? null, null, $rec, ['bulk'=>1,'undo'=>1]); }
                $restored++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }
        // Invalidate token
        unset($_SESSION['_bulk_undo'][$token]);
        Flash::success(__('Restored: ') . $restored . ' · ' . __('Failed: ') . $failed);
        // Redirect back to entity list
        redirect('/' . $entity);
    }

    private function storeFor(string $entity): ?object
    {
        return match($entity) {
            'contacts' => $this->contactsStore,
            'deals' => $this->dealsStore,
            'projects' => $this->projectsStore,
            default => null,
        };
    }
}
