<?php

declare(strict_types=1);

namespace App\Util;

use App\Domain\Schemas;
use App\Http\Request;

class ListSort
{
    /**
     * Render a generic list view with filtering, sorting, and pagination.
     * - Request is injected (no direct superglobals usage)
     * - Filterable/searchable fields are derived from Schemas unless explicitly provided
     * - Sorting is null-safe, locale-aware (if intl available), and numeric-aware
     *
     * @param Request $request Current HTTP request
     * @param string $type Singular type label for title (e.g., "Contact")
     * @param string $schema Schema key (e.g., "contacts")
     * @param object $store Data store exposing all(): array
     * @param array<string>|null $allowed Optional list of allowed sort fields; defaults to schema column names
     * @param array<string>|null $searchFields Optional list of fields to search; defaults from schema fields/columns
     */
    public static function getSortedList(Request $request, string $type, string $schema, object $store, ?array $allowed = null, ?array $searchFields = null): void
    {
        $path = current_path();
        // Saved views support: load available views and apply selected view defaults
        $views = [];
        $currentViewId = (int)($request->get('view') ?? 0);
        $viewCfg = [];
        try {
            global $container;
            /** @var object $viewsStore */
            $viewsStore = $container->get('viewsStore');
            $allViews = $viewsStore->all();
            if (is_array($allViews)) {
                foreach ($allViews as $v) {
                    if (($v['entity'] ?? '') === $schema) {
                        $views[] = $v;
                    }
                    if ($currentViewId > 0 && (int)($v['id'] ?? 0) === $currentViewId) {
                        $viewCfg = is_array($v['config'] ?? null) ? $v['config'] : [];
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore if views store not available
        }

        $q = trim((string)($request->get('q') ?? ($viewCfg['q'] ?? '')));
        $tag = trim((string)($request->get('tag') ?? ($viewCfg['tag'] ?? '')));
        $sort = (string)($request->get('sort') ?? ($viewCfg['sort'] ?? 'name'));
        $dir = strtolower((string)($request->get('dir') ?? ($viewCfg['dir'] ?? 'asc'))) === 'desc' ? 'desc' : 'asc';
        $page = (int)($request->get('page') ?? 1);
        $per = (int)($request->get('per') ?? ($viewCfg['per'] ?? 10));
        if ($page < 1) { $page = 1; }
        if ($per < 1) { $per = 10; }
        if ($per > 100) { $per = 100; }

        $schemaDef = Schemas::get($schema);
        $columnNames = array_map(fn($c) => (string)($c['name'] ?? ''), $schemaDef['columns']);
        $columnNames = array_values(array_filter($columnNames, fn($n) => $n !== ''));

        if ($allowed === null || $allowed === []) { $allowed = $columnNames; }

        if ($searchFields === null) {
            $searchableFromFields = [];
            foreach ($schemaDef['fields'] as $f) {
                $type = strtolower((string)($f['type'] ?? 'text'));
                if (in_array($type, ['text','textarea','email','tel','select'], true)) {
                    $name = (string)($f['name'] ?? '');
                    if ($name !== '') { $searchableFromFields[] = $name; }
                }
            }
            $searchFields = array_values(array_unique(array_merge($columnNames, $searchableFromFields)));
        }

        $items = $store->all();

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $items = array_values(array_filter($items, function($it) use ($needle, $searchFields) {
                foreach ($searchFields as $field) {
                    $val = $it[$field] ?? null;
                    if ($val === null) { continue; }
                    $v = mb_strtolower((string)$val);
                    if ($v !== '' && str_contains($v, $needle)) {
                        return true;
                    }
                }
                return false;
            }));
        }
        if ($tag !== '') {
            $tagNeedle = mb_strtolower($tag);
            $items = array_values(array_filter($items, function($it) use ($tagNeedle) {
                $tags = $it['tags'] ?? [];
                if (!is_array($tags)) { return false; }
                foreach ($tags as $t) {
                    $tv = mb_strtolower((string)$t);
                    if ($tv === '') { continue; }
                    if ($tv === $tagNeedle || str_contains($tv, $tagNeedle)) { return true; }
                }
                return false;
            }));
        }

        if (!in_array($sort, $allowed, true)) { $sort = $allowed[0] ?? 'name'; }

        // Decorate for stable sort
        $decorated = [];
        foreach ($items as $idx => $row) {
            $decorated[] = ['__i' => $idx, '__v' => $row[$sort] ?? null, '__row' => $row];
        }

        $collator = class_exists(\Collator::class) ? new \Collator(\Locale::getDefault()) : null;

        usort($decorated, function($a, $b) use ($dir, $collator) {
            $va = $a['__v'];
            $vb = $b['__v'];
            if ($va === $vb) {
                // stable by original index
                return $a['__i'] <=> $b['__i'];
            }
            // nulls last in asc, first in desc
            if ($va === null) { return $dir === 'asc' ? 1 : -1; }
            if ($vb === null) { return $dir === 'asc' ? -1 : 1; }

            // numeric-aware if both numeric
            if (is_numeric($va) && is_numeric($vb)) {
                $cmp = (float)$va <=> (float)$vb;
                return $dir === 'asc' ? $cmp : -$cmp;
            }
            $sa = (string)$va; $sb = (string)$vb;
            if ($collator) {
                $cmp = $collator->compare($sa, $sb);
            } else {
                // natural, case-insensitive compare as fallback
                $cmp = strnatcasecmp($sa, $sb);
            }
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        $items = array_map(fn($d) => $d['__row'], $decorated);

        $total = count($items);

        // HTTP caching for list endpoints (ETag/Last-Modified)
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            $lastModTs = 0;
            if (is_object($store) && method_exists($store, 'lastModified')) {
                try { $lastModTs = (int)$store->lastModified(); } catch (\Throwable) { $lastModTs = 0; }
            }
            $etag = 'W/"' . sha1($schema . '|' . $lastModTs . '|' . $total . '|' . $sort . '|' . $dir . '|' . $q . '|' . $tag . '|' . $page . '|' . $per) . '"';
            header('ETag: ' . $etag);
            header('Cache-Control: private, must-revalidate');
            if ($lastModTs > 0) {
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModTs) . ' GMT');
            }
            $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
            $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
            $imsTs = $ifModifiedSince !== '' ? strtotime($ifModifiedSince) : false;
            if (($ifNoneMatch !== '' && trim($ifNoneMatch) === $etag) || ($imsTs !== false && $lastModTs > 0 && $lastModTs <= $imsTs)) {
                http_response_code(304);
                return; // Not Modified
            }
        }

        $offset = ($page - 1) * $per;
        $paged = $offset >= 0 ? array_slice($items, $offset, $per) : $items;

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
            'tag' => $tag,
            'path' => $path,
            'columns' => $schemaDef['columns'],
            'views' => $views,
            'currentViewId' => $currentViewId,
        ]);
    }
}