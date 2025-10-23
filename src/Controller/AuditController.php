<?php

declare(strict_types=1);

namespace App\Controller;

final class AuditController
{
    public function __construct(
        private readonly object $auditStore
    ) {}

    public function list(): void
    {
        $all = $this->auditStore->all();
        // Sort by date desc if present
        usort($all, function($a,$b){
            $da = (string)($a['at'] ?? '');
            $db = (string)($b['at'] ?? '');
            return strcmp($db, $da);
        });
        render('admin/audit', [
            'title' => __('Audit log'),
            'entries' => $all,
        ]);
    }
}
