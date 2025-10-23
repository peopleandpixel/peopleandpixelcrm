<?php

declare(strict_types=1);

namespace App\Controller;

use function render;
use function url;
use function __;

final class DashboardController
{
    public function __construct(
        private readonly object $tasksStore,
        private readonly object $contactsStore,
        private readonly object $storageStore,
    ) {}

    public function index(): void
    {
        // Simple per-request memoization
        static $cache = null;
        if ($cache !== null) {
            render('dashboard', $cache);
            return;
        }

        $now = new \DateTimeImmutable('now');
        $in7 = $now->add(new \DateInterval('P7D'));

        // Tasks by status and upcoming reminders
        $tasks = $this->tasksStore->all();
        // Build contact name map for display
        $contactsMap = [];
        foreach ($this->contactsStore->all() as $c) {
            $contactsMap[(int)($c['id'] ?? 0)] = (string)($c['name'] ?? '');
        }
        $statuses = ['open','in_progress','review','blocked','done'];
        $tasksByStatus = array_fill_keys($statuses, 0);
        $upcoming = [];
        foreach ($tasks as $t) {
            $st = (string)($t['status'] ?? 'open');
            if (!isset($tasksByStatus[$st])) { $st = 'open'; }
            $tasksByStatus[$st]++;
            // attach contact name if available
            $cid = (int)($t['contact_id'] ?? 0);
            if ($cid && empty($t['contact_name'])) {
                $t['contact_name'] = $contactsMap[$cid] ?? '';
            }
            $rem = (string)($t['reminder_at'] ?? '');
            if ($rem !== '') {
                try {
                    $rdt = new \DateTimeImmutable($rem);
                    if ($rdt <= $in7) {
                        $t['__reminder_dt'] = $rdt;
                        $t['__overdue'] = $rdt <= $now;
                        $upcoming[] = $t;
                    }
                } catch (\Throwable) {
                    // ignore parse errors
                }
            }
        }
        usort($upcoming, function($a,$b){
            return ($a['__reminder_dt'] <=> $b['__reminder_dt']);
        });
        // Limit to 10 entries
        $upcoming = array_slice($upcoming, 0, 10);

        // Recent contacts
        $contacts = $this->contactsStore->all();
        usort($contacts, function($a,$b){
            $ad = (string)($a['created_at'] ?? '');
            $bd = (string)($b['created_at'] ?? '');
            return strcmp($bd, $ad); // desc
        });
        $recentContacts = array_slice($contacts, 0, 5);

        // Low stock items
        $storage = $this->storageStore->all();
        $lowStock = [];
        foreach ($storage as $s) {
            $qty = (float)($s['quantity'] ?? ($s['stock'] ?? 0));
            $thr = (float)($s['low_stock_threshold'] ?? 0);
            $isLow = $thr > 0 ? ($qty <= $thr) : ($qty <= 0);
            if ($isLow) { $lowStock[] = $s + ['__qty' => $qty, '__thr' => $thr]; }
        }
        usort($lowStock, function($a,$b){ return $a['__qty'] <=> $b['__qty']; });
        $lowStock = array_slice($lowStock, 0, 5);

        $cache = [
            'tasksByStatus' => $tasksByStatus,
            'upcoming' => $upcoming,
            'recentContacts' => $recentContacts,
            'lowStock' => $lowStock,
        ];
        render('dashboard', $cache);
    }
}
