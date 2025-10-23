<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;

final class ReportService
{
    private Config $config;
    private object $contactsStore;
    private object $tasksStore;
    private object $dealsStore;
    private object $projectsStore;
    private object $timesStore;
    private object $paymentsStore;

    public function __construct(Config $config, object $contactsStore, object $tasksStore, object $dealsStore, object $projectsStore, object $timesStore, object $paymentsStore)
    {
        $this->config = $config;
        $this->contactsStore = $contactsStore;
        $this->tasksStore = $tasksStore;
        $this->dealsStore = $dealsStore;
        $this->projectsStore = $projectsStore;
        $this->timesStore = $timesStore;
        $this->paymentsStore = $paymentsStore;
    }

    /**
     * Execute a report definition and return structured result for rendering and CSV export.
     * @param array{name:string,entity:string,filters:array,group_by:string,metric:string,period?:string} $def
     * @return array{title:string, headers:array<int,string>, rows:array<int,array{label:string,value:float,count:int}>, total:float, series?:array}
     */
    public function run(array $def): array
    {
        $entity = (string)($def['entity'] ?? 'tasks');
        $groupBy = (string)($def['group_by'] ?? 'status');
        $metric = (string)($def['metric'] ?? 'count');
        $filters = is_array($def['filters'] ?? null) ? $def['filters'] : [];
        $period = (string)($def['period'] ?? 'month'); // for date groupings: day|month

        $cacheKey = $this->makeCacheKey($def);
        $cached = $this->cacheGet($cacheKey);
        if ($cached !== null) { return $cached; }

        $data = $this->loadData($entity);
        $data = $this->applyFilters($entity, $data, $filters);

        // Aggregate by requested grouping
        $buckets = [];
        $total = 0.0;

        foreach ($data as $row) {
            [$label, $value] = $this->extractGroupAndValue($entity, $row, $groupBy, $metric, $period);
            if ($label === null) { $label = 'N/A'; }
            if (!isset($buckets[$label])) { $buckets[$label] = ['count' => 0, 'value' => 0.0]; }
            $buckets[$label]['count'] += 1;
            $buckets[$label]['value'] += (float)$value;
            $total += (float)$value;
        }

        // Build rows sorted by value desc
        uasort($buckets, fn($a,$b) => ($b['value'] <=> $a['value']) ?: ($b['count'] <=> $a['count']));
        $rows = [];
        foreach ($buckets as $label => $agg) {
            $rows[] = [
                'label' => (string)$label,
                'value' => (float)$agg['value'],
                'count' => (int)$agg['count'],
            ];
        }

        $result = [
            'title' => (string)($def['name'] ?? 'Report'),
            'headers' => ['Label', $metric === 'count' ? 'Count' : 'Total', 'Items'],
            'rows' => $rows,
            'total' => $total,
        ];

        $this->cacheSet($cacheKey, $result, 300);
        return $result;
    }

    /**
     * Load raw data for an entity using the underlying store.
     * @return array<int,array<string,mixed>>
     */
    private function loadData(string $entity): array
    {
        return match ($entity) {
            'contacts' => $this->contactsStore->all(),
            'tasks' => $this->tasksStore->all(),
            'deals' => $this->dealsStore->all(),
            'projects' => $this->projectsStore->all(),
            'times' => $this->timesStore->all(),
            'payments' => $this->paymentsStore->all(),
            default => [],
        };
    }

    /**
     * Apply minimal filters. Supports exact equals for scalar fields, and date range on these known date keys.
     * @param array<int,array<string,mixed>> $data
     * @param array<string,mixed> $filters
     */
    private function applyFilters(string $entity, array $data, array $filters): array
    {
        $dateKey = match ($entity) {
            'tasks' => 'due_date',
            'contacts' => 'created_at',
            'deals' => 'created_at',
            'projects' => 'start_date',
            'times' => 'date',
            'payments' => 'date',
            default => null,
        };

        $from = isset($filters['from']) ? (string)$filters['from'] : null;
        $to = isset($filters['to']) ? (string)$filters['to'] : null;
        unset($filters['from'],$filters['to']);

        return array_values(array_filter($data, function($row) use ($filters, $dateKey, $from, $to) {
            // Equals filters
            foreach ($filters as $k => $v) {
                if (!isset($row[$k])) return false;
                $val = is_array($row[$k]) ? $row[$k] : [$row[$k]];
                if (is_array($row[$k])) {
                    if (!in_array($v, $val, true)) return false;
                } else {
                    if ((string)$row[$k] !== (string)$v) return false;
                }
            }
            // Date range
            if ($dateKey !== null) {
                $d = (string)($row[$dateKey] ?? '');
                if ($from && $d < $from) return false;
                if ($to && $d > $to) return false;
            }
            return true;
        }));
    }

    /**
     * Return [label, numericValue] for aggregation.
     */
    private function extractGroupAndValue(string $entity, array $row, string $groupBy, string $metric, string $period): array
    {
        // label
        $label = null;
        switch ($groupBy) {
            case 'status':
                $label = (string)($row['status'] ?? 'Unknown');
                break;
            case 'owner':
            case 'owner_id':
                $label = (string)($row['owner'] ?? $row['owner_id'] ?? 'Unassigned');
                break;
            case 'tag':
            case 'tags':
                $tags = $row['tags'] ?? [];
                if (is_array($tags) && count($tags) > 0) {
                    $label = implode(',', array_map('strval', $tags));
                } else {
                    $label = 'No tags';
                }
                break;
            case 'date':
                $key = match ($entity) {
                    'tasks' => 'due_date',
                    'contacts' => 'created_at',
                    'deals' => 'created_at',
                    'projects' => 'start_date',
                    'times' => 'date',
                    'payments' => 'date',
                    default => null,
                };
                $d = $key ? (string)($row[$key] ?? '') : '';
                if ($d === '') { $label = 'Unknown'; }
                else {
                    $label = $period === 'day' ? $d : substr($d, 0, 7); // YYYY-MM for month
                }
                break;
            default:
                $label = (string)($row[$groupBy] ?? 'N/A');
        }

        // value
        $value = 1.0;
        if ($metric === 'count') {
            $value = 1.0;
        } elseif ($metric === 'sum_amount') {
            $value = (float)($row['amount'] ?? 0);
        } elseif ($metric === 'sum_time') {
            $mins = (float)($row['minutes'] ?? $row['duration'] ?? 0);
            $value = $mins; // keep minutes
        }
        return [$label, $value];
    }

    private function makeCacheKey(array $def): string
    {
        return md5(json_encode($def));
    }

    private function cacheDir(): string
    {
        $dir = $this->config->getCacheDir() . '/reports';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        return $dir;
    }

    private function cacheGet(string $key): ?array
    {
        $file = $this->cacheDir() . '/' . $key . '.json';
        if (!is_file($file)) return null;
        $ttl = 300; // seconds
        if (filemtime($file) < time() - $ttl) { return null; }
        $raw = @file_get_contents($file);
        if ($raw === false) return null;
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private function cacheSet(string $key, array $value, int $ttl): void
    {
        $file = $this->cacheDir() . '/' . $key . '.json';
        @file_put_contents($file, json_encode($value));
        // TTL is enforced on read via filemtime
    }
}
