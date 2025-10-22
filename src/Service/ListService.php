<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Generic list helper: filtering, sorting, and pagination.
 * Minimalistic to avoid over-engineering; can be extended later with schemas.
 */
class ListService
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @param string $q
     * @param array<int, string> $fields
     * @return array<int, array<string, mixed>>
     */
    public function filter(array $items, string $q, array $fields): array
    {
        $q = trim($q);
        if ($q === '') { return array_values($items); }
        $needle = mb_strtolower($q);
        return array_values(array_filter($items, function(array $it) use ($needle, $fields) {
            foreach ($fields as $field) {
                $v = (string)($it[$field] ?? '');
                if ($v !== '' && str_contains(mb_strtolower($v), $needle)) { return true; }
            }
            return false;
        }));
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param string $key
     * @param string $dir 'asc'|'desc'
     * @param array<int, string> $allowedKeys
     * @return array<int, array<string, mixed>>
     */
    public function sort(array $items, string $key, string $dir, array $allowedKeys): array
    {
        if (!in_array($key, $allowedKeys, true)) { $key = $allowedKeys[0] ?? 'id'; }
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';
        usort($items, function(array $a, array $b) use ($key, $dir) {
            $va = (string)($a[$key] ?? '');
            $vb = (string)($b[$key] ?? '');
            $cmp = strcmp($va, $vb);
            return $dir === 'asc' ? $cmp : -$cmp;
        });
        return $items;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param int $page
     * @param int $per
     * @return array{total:int, page:int, per:int, items:array<int, array<string, mixed>>}
     */
    public function paginate(array $items, int $page, int $per): array
    {
        $total = count($items);
        $page = max(1, $page);
        $per = max(1, min(100, $per));
        $offset = ($page - 1) * $per;
        $paged = array_slice($items, $offset, $per);
        return ['total' => $total, 'page' => $page, 'per' => $per, 'items' => $paged];
    }
}
