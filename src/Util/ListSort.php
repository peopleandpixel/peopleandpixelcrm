<?php

namespace App\Util;

use App\Domain\Schemas;

class ListSort
{

    public static function getSortedList(string $type, string $schema, object $store, array $allowed): void
    {
        $path = current_path();
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'name';
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = max(1, min(100, (int)($_GET['per'] ?? 10)));

        $items = $store->all();

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $items = array_values(array_filter($items, function($it) use ($needle) {
                foreach (['name','email','company','phone','notes'] as $field) {
                    $v = (string)($it[$field] ?? '');
                    if ($v !== '' && str_contains(mb_strtolower($v), $needle)) {
                        return true;
                    }
                }
                return false;
            }));
        }

        //$allowed = ;
        if (!in_array($sort, $allowed, true)) { $sort = 'name'; }
        usort($items, function($a, $b) use ($sort, $dir) {
            $va = (string)($a[$sort] ?? '');
            $vb = (string)($b[$sort] ?? '');
            $cmp = strcmp($va, $vb);
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        $total = count($items);
        $offset = ($page - 1) * $per;
        $paged = array_slice($items, $offset, $per);

        $schemaColumns = Schemas::get($schema);
        render('generic_list', [
            'type' => $type,
            'schema' => $schema,
            'items' => $paged,
            'total' => $total,
            'page' => $page,
            'per' => $per,
            'sort' => $sort,
            'dir' => $dir,
            'q' => $q,
            'path' => $path,
            'columns' => $schemaColumns['columns']
        ]);
    }

}