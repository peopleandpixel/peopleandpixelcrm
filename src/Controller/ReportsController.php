<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ReportService;

final class ReportsController
{
    private ReportService $reportService;
    private object $reportsStore;

    public function __construct(ReportService $reportService, object $reportsStore)
    {
        $this->reportService = $reportService;
        $this->reportsStore = $reportsStore;
    }

    public function list(): void
    {
        $reports = $this->reportsStore->all();
        render('reports/list', [
            'title' => __('Reports'),
            'reports' => $reports,
        ]);
    }

    public function newForm(): void
    {
        render('reports/new', [
            'title' => __('New report'),
        ]);
    }

    public function create(): void
    {
        $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
        $entity = isset($_POST['entity']) ? (string)$_POST['entity'] : 'tasks';
        $groupBy = isset($_POST['group_by']) ? (string)$_POST['group_by'] : 'status';
        $metric = isset($_POST['metric']) ? (string)$_POST['metric'] : 'count';
        $period = isset($_POST['period']) ? (string)$_POST['period'] : 'month';
        $filters = [];
        foreach (['status','owner_id','from','to','tag'] as $k) {
            if (isset($_POST[$k]) && $_POST[$k] !== '') {
                $filters[$k] = (string)$_POST[$k];
            }
        }
        $item = [
            'name' => $name !== '' ? $name : ($entity . ' ' . $groupBy . ' ' . $metric),
            'entity' => $entity,
            'group_by' => $groupBy,
            'metric' => $metric,
            'period' => $period,
            'filters' => $filters,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->reportsStore->add($item);
        redirect('/reports');
    }

    public function run(): void
    {
        // Either use a saved report by id or ad-hoc params
        $id = isset($_GET['id']) ? (string)$_GET['id'] : null;
        $def = null;
        if ($id !== null) {
            $all = $this->reportsStore->all();
            foreach ($all as $r) { if ((string)($r['id'] ?? '') === $id) { $def = $r; break; } }
        }
        if ($def === null) {
            $def = [
                'name' => (string)($_GET['name'] ?? 'Ad-hoc report'),
                'entity' => (string)($_GET['entity'] ?? 'tasks'),
                'group_by' => (string)($_GET['group_by'] ?? 'status'),
                'metric' => (string)($_GET['metric'] ?? 'count'),
                'period' => (string)($_GET['period'] ?? 'month'),
                'filters' => [
                    'from' => isset($_GET['from']) ? (string)$_GET['from'] : null,
                    'to' => isset($_GET['to']) ? (string)$_GET['to'] : null,
                    'status' => isset($_GET['status']) ? (string)$_GET['status'] : null,
                    'owner_id' => isset($_GET['owner_id']) ? (string)$_GET['owner_id'] : null,
                    'tag' => isset($_GET['tag']) ? (string)$_GET['tag'] : null,
                ],
            ];
            // cleanup nulls
            $def['filters'] = array_filter($def['filters'], fn($v) => $v !== null && $v !== '');
        }

        $result = $this->reportService->run($def);
        render('reports/run', [
            'title' => $result['title'],
            'report' => $result,
            'def' => $def,
        ]);
    }

    public function exportCsv(): void
    {
        $name = (string)($_GET['name'] ?? 'report');
        $def = [
            'name' => $name,
            'entity' => (string)($_GET['entity'] ?? 'tasks'),
            'group_by' => (string)($_GET['group_by'] ?? 'status'),
            'metric' => (string)($_GET['metric'] ?? 'count'),
            'period' => (string)($_GET['period'] ?? 'month'),
            'filters' => [
                'from' => isset($_GET['from']) ? (string)$_GET['from'] : null,
                'to' => isset($_GET['to']) ? (string)$_GET['to'] : null,
                'status' => isset($_GET['status']) ? (string)$_GET['status'] : null,
                'owner_id' => isset($_GET['owner_id']) ? (string)$_GET['owner_id'] : null,
                'tag' => isset($_GET['tag']) ? (string)$_GET['tag'] : null,
            ],
        ];
        $def['filters'] = array_filter($def['filters'], fn($v) => $v !== null && $v !== '');
        $res = $this->reportService->run($def);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_-]+/','_', strtolower($name)) . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Label', 'Value', 'Count']);
        foreach ($res['rows'] as $row) {
            fputcsv($out, [$row['label'], (string)$row['value'], (string)$row['count']]);
        }
        fclose($out);
    }
}
