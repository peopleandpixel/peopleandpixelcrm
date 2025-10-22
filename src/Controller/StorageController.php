<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Schemas;
use App\Domain\StorageItem as StorageItemDTO;
use App\Util\Flash;
use JetBrains\PhpStorm\NoReturn;
use function redirect;
use function render;

class StorageController
{
    public function __construct(
        private readonly object $storageStore,
        private readonly ?object $storageAdjustmentsStore = null,
    ) {}

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->storageStore->get($id) : null;
        if (!$item) { redirect('/storage'); }
        $schema = Schemas::get('storage');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Storage item') . ': ' . ($item['name'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/storage'),
            'edit_url' => url('/storage/edit', ['id' => $id])
        ]);
    }

    public function list(): void
    {
        $path = current_path();
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'name';
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = max(1, min(100, (int)($_GET['per'] ?? 10)));

        $items = $this->storageStore->all();
        // Optional low-stock filter
        $onlyLow = isset($_GET['low']) && (string)$_GET['low'] === '1';
        if ($onlyLow) {
            $items = array_values(array_filter($items, function(array $it): bool {
                $qty = (int)($it['quantity'] ?? 0);
                $thr = (int)($it['low_stock_threshold'] ?? 0);
                return $qty <= $thr;
            }));
        }
        // Search filter
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $items = array_values(array_filter($items, function($it) use ($needle) {
                foreach (['sku','name','category','location','notes'] as $field) {
                    $v = (string)($it[$field] ?? '');
                    if ($v !== '' && str_contains(mb_strtolower($v), $needle)) { return true; }
                }
                $qty = (string)($it['quantity'] ?? '');
                if ($qty !== '' && str_contains(mb_strtolower((string)$qty), $needle)) { return true; }
                return false;
            }));
        }
        // Sorting
        $allowed = ['name','category','location','quantity'];
        if (!in_array($sort, $allowed, true)) { $sort = 'name'; }
        usort($items, function($a,$b) use ($sort, $dir) {
            if ($sort === 'quantity') {
                $va = (int)($a['quantity'] ?? 0); $vb = (int)($b['quantity'] ?? 0);
                $cmp = $va <=> $vb;
            } else {
                $va = (string)($a[$sort] ?? ''); $vb = (string)($b[$sort] ?? '');
                $cmp = strcmp($va, $vb);
            }
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        $total = count($items);
        $offset = ($page - 1) * $per;
        $paged = array_slice($items, $offset, $per);

        $schema = Schemas::get('storage');
        render('storage_list', [
            'items' => $paged,
            'columns' => $schema['columns'],
            'onlyLow' => $onlyLow,
            'total' => $total,
            'page' => $page,
            'per' => $per,
            'sort' => $sort,
            'dir' => $dir,
            'q' => $q,
            'path' => $path,
        ]);
    }

    public function newForm(): void
    {
        $schema = Schemas::get('storage');
        render('storage_add', [
            'fields' => $schema['fields'],
            'cancel_url' => url('/storage')
        ]);
    }

    public static function create(object $storageStore): void
    {
        $dto = StorageItemDTO::fromInput($_POST);
        $errors = $dto->validate();
        // SKU uniqueness check if provided
        $sku = trim((string)($dto->sku ?? ''));
        if ($sku !== '') {
            foreach ($storageStore->all() as $it) {
                if (strcasecmp($sku, (string)($it['sku'] ?? '')) === 0) {
                    $errors['sku'] = __('SKU must be unique');
                    break;
                }
            }
        }
        if (!empty($errors)) {
            $error = 'Please fix the highlighted errors.';
            $schema = Schemas::get('storage');
            render('storage_add', ['error' => $error, 'errors' => $errors, 'fields' => $schema['fields'], 'cancel_url' => url('/storage')] + $dto->toArray());
            return;
        }
        $storageStore->add($dto->toArray() + [
            'created_at' => \App\Util\Dates::nowAtom(),
        ]);
        Flash::success(__('Item created successfully.'));
        redirect('/storage');
    }

    public static function editForm(object $storageStore): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $storageStore->get($id) : null;
        if (!$item) { redirect('/storage'); }
        $schema = Schemas::get('storage');
        render('storage_add', ['edit' => true, 'item' => $item, 'fields' => $schema['fields'], 'cancel_url' => url('/storage')] + $item);
    }

    public static function update(object $storageStore): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/storage'); }
        $dto = StorageItemDTO::fromInput($_POST);
        $errors = $dto->validate();
        // SKU uniqueness check excluding current item
        $sku = trim((string)($dto->sku ?? ''));
        if ($sku !== '') {
            foreach ($storageStore->all() as $it) {
                if ((int)($it['id'] ?? 0) === $id) { continue; }
                if (strcasecmp($sku, (string)($it['sku'] ?? '')) === 0) {
                    $errors['sku'] = __('SKU must be unique');
                    break;
                }
            }
        }
        if (!empty($errors)) {
            $error = 'Please fix the highlighted errors.';
            $item = $storageStore->get($id) ?? [];
            $schema = Schemas::get('storage');
            render('storage_add', ['error' => $error, 'errors' => $errors, 'item' => $item, 'fields' => $schema['fields'], 'cancel_url' => url('/storage')] + $dto->toArray());
            return;
        }
        $storageStore->update($id, $dto->toArray());
        Flash::success(__('Item updated successfully.'));
        redirect('/storage');
    }

    #[NoReturn]
    public static function delete(object $storageStore): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { $storageStore->delete($id); }
        Flash::success(__('Item deleted.'));
        redirect('/storage');
    }

    /**
     * Adjusts stock quantity for an item and records an adjustment history entry.
     * Validation rules:
     * - delta must be non-zero integer
     * - when negative inventory is not allowed, the resulting quantity must not go below zero
     * Transactional behavior:
     * - Both the quantity update and the history append happen within a transaction lock
     * - On any failure, attempts to roll back the quantity update and surfaces a Flash error
     */
    public static function adjust(object $storageStore, object $adjustmentsStore, ?\App\Config $config = null): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $delta = (int)($_POST['delta'] ?? 0);
        $note = isset($_POST['note']) ? trim((string)$_POST['note']) : '';
        if ($id <= 0 || $delta === 0) { Flash::error(__('Invalid adjustment.')); redirect('/storage'); }

        $item = $storageStore->get($id);
        if (!$item) { Flash::error(__('Item not found.')); redirect('/storage'); }

        $currentQty = (int)($item['quantity'] ?? 0);
        $resultQty = $currentQty + $delta;
        $allowNeg = $config?->isInventoryNegativeAllowed() ?? false;
        if (!$allowNeg && $resultQty < 0) {
            Flash::error(__('Adjustment would result in negative stock, which is not allowed.'));
            redirect('/storage');
        }

        $now = \App\Util\Dates::nowAtom();
        $rollbackNeeded = false;
        try {
            // Guard multi-write sequence with a transaction on the storage store
            $storageStore->withTransaction(function() use ($storageStore, $adjustmentsStore, $id, $resultQty, $delta, $note, $now, &$rollbackNeeded) {
                $storageStore->update($id, ['quantity' => $resultQty]);
                $rollbackNeeded = true; // after this point we need to roll back if add() fails
                // Adjustment history model clarified: record resulting quantity for reliable audit trail
                $adjustmentsStore->add([
                    'item_id' => $id,
                    'delta' => $delta,
                    'note' => $note,
                    'resulting_quantity' => $resultQty,
                    'created_at' => $now,
                ]);
            });
            Flash::success(__('Stock adjusted.'));
        } catch (\Throwable $e) {
            // Best-effort rollback if we already updated the item quantity
            if ($rollbackNeeded) {
                try { $storageStore->update($id, ['quantity' => $currentQty]); } catch (\Throwable) {}
            }
            Flash::error(__('Failed to adjust stock: ') . $e->getMessage());
        }
        redirect('/storage');
    }

    public static function history(object $storageStore, object $adjustmentsStore): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $storageStore->get($id) : null;
        if (!$item) { redirect('/storage'); }
        $rows = array_values(array_filter($adjustmentsStore->all(), fn($r) => (int)($r['item_id'] ?? 0) === $id));
        usort($rows, fn($a,$b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        // Map to view model expected by template
        $history = array_map(function(array $r) use ($item) {
            return [
                'created_at' => $r['created_at'] ?? null,
                'item_name' => (string)($item['name'] ?? ''),
                'delta' => (int)($r['delta'] ?? 0),
                'note' => (string)($r['note'] ?? ''),
                'resulting_quantity' => isset($r['resulting_quantity']) ? (int)$r['resulting_quantity'] : null,
            ];
        }, $rows);
        render('storage_history', ['item' => $item, 'history' => $history]);
    }
}
