<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\BackupService;
use App\Util\Csrf;
use App\Util\Flash;

final class BackupsController
{
    public function __construct(private readonly BackupService $service)
    {
    }

    public function list(): void
    {
        $snapshots = $this->service->listSnapshots();
        render('admin/backups', [
            'title' => __('Backups'),
            'snapshots' => $snapshots,
            'retention' => $this->service->getRetention(),
        ]);
    }

    public function create(): void
    {
        $t = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($t) ? $t : null)) { http_response_code(400); render('errors/400'); return; }
        try {
            $path = $this->service->createSnapshot();
            Flash::success(__('Backup created: ') . basename($path));
        } catch (\Throwable $e) {
            Flash::error(__('Backup failed: ') . $e->getMessage());
        }
        redirect('/admin/backups');
    }

    public function verify(): void
    {
        $t = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($t) ? $t : null)) { http_response_code(400); render('errors/400'); return; }
        $file = (string)($_POST['file'] ?? '');
        try {
            $res = $this->service->verifySnapshot($file);
            if ($res['ok']) { Flash::success(__('Snapshot verified OK.')); }
            else { Flash::error(__('Snapshot verification failed: ') . implode('; ', $res['errors'])); }
        } catch (\Throwable $e) {
            Flash::error(__('Verification failed: ') . $e->getMessage());
        }
        redirect('/admin/backups');
    }

    public function restore(): void
    {
        $t = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($t) ? $t : null)) { http_response_code(400); render('errors/400'); return; }
        $file = (string)($_POST['file'] ?? '');
        $confirm = (string)($_POST['confirm'] ?? '') === 'yes';
        if (!$confirm) { Flash::error(__('Please confirm restore.')); redirect('/admin/backups'); }
        try {
            $this->service->restoreSnapshot($file);
            Flash::success(__('Restore completed.'));
        } catch (\Throwable $e) {
            Flash::error(__('Restore failed: ') . $e->getMessage());
        }
        redirect('/admin/backups');
    }

    public function download(): void
    {
        $file = (string)($_GET['file'] ?? '');
        try {
            $list = $this->service->listSnapshots();
            $names = array_column($list, 'file');
            if ($file === '' || !in_array($file, $names, true)) { http_response_code(404); render('errors/404', ['path' => '/admin/backups/download']); return; }
            $path = $this->service->getBackupDir() . '/' . $file;
            if (!is_file($path)) { http_response_code(404); render('errors/404', ['path' => '/admin/backups/download']); return; }
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
            readfile($path);
        } catch (\Throwable) {
            http_response_code(500);
            render('errors/500');
        }
    }

    public function delete(): void
    {
        $t = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($t) ? $t : null)) { http_response_code(400); render('errors/400'); return; }
        $file = (string)($_POST['file'] ?? '');
        try {
            if ($this->service->deleteSnapshot($file)) { Flash::success(__('Snapshot deleted.')); }
            else { Flash::error(__('Failed to delete snapshot.')); }
        } catch (\Throwable $e) {
            Flash::error(__('Delete failed: ') . $e->getMessage());
        }
        redirect('/admin/backups');
    }
}
