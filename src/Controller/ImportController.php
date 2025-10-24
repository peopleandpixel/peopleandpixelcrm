<?php

declare(strict_types=1);

namespace App\Controller;

use App\Container;
use App\Util\Csrf;
use App\Util\Flash;
use JetBrains\PhpStorm\NoReturn;
use function render;
use function redirect;

class ImportController
{
    public static function form(): void
    {
        $entities = self::supportedEntities();
        render('import_form', ['entities' => $entities]);
    }

    /**
     * @throws \Random\RandomException
     */
    #[NoReturn]
    public static function submit(Container $container): void
    {
        $csrf = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($csrf) ? $csrf : null)) {
            render('errors/405', ['path' => '/import', 'allowed' => ['POST']]);
            exit;
        }

        $entity = isset($_POST['entity']) ? strtolower(trim((string)$_POST['entity'])) : '';
        $format = isset($_POST['format']) && strtolower((string)$_POST['format']) === 'csv' ? 'csv' : 'json';
        $strategy = isset($_POST['strategy']) ? strtolower((string)$_POST['strategy']) : 'insert';
        if (!in_array($strategy, ['insert','upsert','merge'], true)) { $strategy = 'insert'; }
        $keyField = isset($_POST['key_field']) ? strtolower((string)$_POST['key_field']) : 'id';
        $dryRun = isset($_POST['dry_run']) && ($_POST['dry_run'] === '1' || $_POST['dry_run'] === 'on');
        $payload = isset($_POST['payload']) ? (string)$_POST['payload'] : '';
        $fallbackNotice = '';

        $errors = [];
        if ($entity === '' || !in_array($entity, self::supportedEntities(), true)) {
            $errors[] = 'Unsupported or missing entity.';
        }
        if ($payload === '') {
            $errors[] = 'No data provided.';
        }
        // Simple size limits
        if (strlen($payload) > 2_000_000) { $errors[] = 'Payload too large (max 2MB).'; }
        if (!empty($errors)) {
            render('import_form', ['entities' => self::supportedEntities(), 'error' => implode(' ', $errors), 'old' => compact('entity','format','payload','dryRun','strategy','keyField')]);
            exit;
        }

        $rows = [];
        try {
            if ($format === 'json') {
                $rows = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($rows)) { $rows = []; }
            } else {
                $rows = self::parseCsv($payload);
            }
        } catch (\Throwable $e) {
            render('import_form', ['entities' => self::supportedEntities(), 'error' => 'Failed to parse input: ' . $e->getMessage(), 'old' => compact('entity','format','payload','dryRun','strategy','keyField')]);
            exit;
        }

        // Normalize: expect list of associative rows
        $items = [];
        foreach ($rows as $r) { if (is_array($r)) { $items[] = $r; } }

        // Schema: restrict to known fields and collect unknown
        $schema = \App\Domain\Schemas::get($entity);
        $allowedFields = array_map(fn($f) => (string)($f['name'] ?? ''), $schema['fields'] ?? []);
        $allowedFields[] = 'id';
        // Validate key field against schema; if not present, fall back to id and inform the user
        if ($keyField !== 'id' && !in_array($keyField, $allowedFields, true)) {
            $fallbackNotice = sprintf("Selected key field '%s' is not available on '%s'; falling back to 'id'.", $keyField, $entity);
            $keyField = 'id';
        }
        $unknownByRow = [];
        foreach ($items as $i => &$row) {
            $unknown = [];
            foreach (array_keys($row) as $k) {
                if (!in_array($k, $allowedFields, true)) { $unknown[] = $k; unset($row[$k]); }
            }
            if (!empty($unknown)) { $unknownByRow[$i] = $unknown; }
        }
        unset($row);

        // Validate per entity using DTO classes
        [$dtoClass, $storeId] = self::entityMap($entity);
        $valid = []; $invalid = [];
        foreach ($items as $idx => $data) {
            $dto = self::makeDto($dtoClass, $data);
            $errs = method_exists($dto, 'validate') ? $dto->validate() : [];
            if (!empty($errs)) {
                $invalid[] = ['index' => $idx, 'errors' => $errs, 'data' => $data];
            } else {
                $valid[] = $dto->toArray();
            }
        }

        // Build preview/actions
        $store = $container->get($storeId);
        $existingAll = is_array(method_exists($store,'all') ? $store->all() : []) ? $store->all() : [];
        $existingByKey = [];
        foreach ($existingAll as $ex) {
            $k = $keyField === 'id' ? (string)($ex['id'] ?? '') : strtolower((string)($ex[$keyField] ?? ''));
            if ($k !== '') { $existingByKey[$k] = $ex; }
        }
        $actions = [];
        foreach ($valid as $idx => $row) {
            $keyVal = $keyField === 'id' ? (string)($row['id'] ?? '') : strtolower((string)($row[$keyField] ?? ''));
            $found = $keyVal !== '' && isset($existingByKey[$keyVal]) ? $existingByKey[$keyVal] : null;
            if (!$found) {
                $actions[] = ['index'=>$idx,'action'=>'create'];
            } else {
                if ($strategy === 'insert') {
                    $actions[] = ['index'=>$idx,'action'=>'skip','reason'=>'exists'];
                } elseif ($strategy === 'upsert') {
                    $actions[] = ['index'=>$idx,'action'=>'update','id'=>$found['id']];
                } else { // merge
                    $actions[] = ['index'=>$idx,'action'=>'merge','id'=>$found['id']];
                }
            }
        }

        // Execute if not dry-run and no invalid
        $created = 0; $updated = 0; $skipped = 0; $merged = 0;
        if (!$dryRun && empty($invalid)) {
            // Backup if using JSON stores
            try {
                /** @var \App\Config $cfg */
                $cfg = $container->get('config');
                if (!$cfg->useDb()) {
                    $path = $cfg->jsonPath($entity . '.json');
                    if (is_file($path)) {
                        $backupDir = $cfg->getVarDir() . '/backups';
                        if (!is_dir($backupDir)) { @mkdir($backupDir, 0777, true); }
                        $stamp = date('Ymd-His');
                        @copy($path, $backupDir . '/' . basename($path) . '.' . $stamp . '.bak');
                    }
                }
            } catch (\Throwable $e) { /* ignore backup issues */ }

            foreach ($actions as $i => $act) {
                $row = $valid[$act['index']];
                if (($act['action'] ?? '') === 'create') {
                    $store->add($row);
                    $created++;
                } elseif (($act['action'] ?? '') === 'update') {
                    $id = (int)$act['id'];
                    $store->update($id, $row);
                    $updated++;
                } elseif (($act['action'] ?? '') === 'merge') {
                    $id = (int)$act['id'];
                    $existing = $store->get($id) ?? [];
                    $mergedRow = $existing;
                    foreach ($row as $k => $v) {
                        if ($k === 'id') continue;
                        if (!isset($mergedRow[$k]) || $mergedRow[$k] === '' || $mergedRow[$k] === null) {
                            $mergedRow[$k] = $v;
                        } elseif ($entity === 'contacts' && $k === 'tags') {
                            $a = is_array($mergedRow['tags'] ?? null) ? $mergedRow['tags'] : [];
                            $b = is_array($row['tags'] ?? null) ? $row['tags'] : [];
                            $mergedRow['tags'] = array_values(array_unique(array_map('strval', array_merge($a, $b))));
                        }
                    }
                    $store->update($id, $mergedRow);
                    $merged++;
                } else {
                    $skipped++;
                }
            }
            if ($fallbackNotice !== '') { \App\Util\Flash::info($fallbackNotice); }
            Flash::success('Import completed. Created: ' . $created . ' · Updated: ' . $updated . ' · Merged: ' . $merged . ' · Skipped: ' . $skipped);
            redirect('/');
        }

        if ($fallbackNotice !== '') { \App\Util\Flash::info($fallbackNotice); }
        render('import_result', [
            'entity' => $entity,
            'format' => $format,
            'dry_run' => $dryRun,
            'total' => count($items),
            'valid_count' => count($valid),
            'invalid_count' => count($invalid),
            'invalid' => $invalid,
            'actions' => $actions,
            'unknown_fields' => $unknownByRow,
            'strategy' => $strategy,
            'key_field' => $keyField,
        ]);
        exit;
    }

    private static function supportedEntities(): array
    {
        return ['contacts','times','tasks','employees','candidates','payments','storage'];
    }

    private static function entityMap(string $entity): array
    {
        // returns [dtoClass, storeId]
        return match ($entity) {
            'contacts' => [\App\Domain\Contact::class, 'contactsStore'],
            'times' => [\App\Domain\TimeEntry::class, 'timesStore'],
            'tasks' => [\App\Domain\Task::class, 'tasksStore'],
            'employees' => [\App\Domain\Employee::class, 'employeesStore'],
            'candidates' => [\App\Domain\Candidate::class, 'candidatesStore'],
            'payments' => [\App\Domain\Payment::class, 'paymentsStore'],
            'storage' => [\App\Domain\StorageItem::class, 'storageStore'],
            default => [\stdClass::class, ''],
        };
    }

    private static function makeDto(string $dtoClass, array $data): object
    {
        if (method_exists($dtoClass, 'fromInput')) {
            return $dtoClass::fromInput($data);
        }
        // Fallback: create from array if constructor supports
        return new $dtoClass($data);
    }

    private static function parseCsv(string $csv): array
    {
        $rows = [];
        $fh = fopen('php://temp', 'r+');
        fwrite($fh, $csv);
        rewind($fh);
        $headers = null;
        while (($cols = fgetcsv($fh)) !== false) {
            if ($headers === null) {
                $headers = array_map(fn($h) => is_string($h) ? trim($h) : '', $cols);
                continue;
            }
            $row = [];
            foreach ($headers as $i => $h) {
                $row[$h] = $cols[$i] ?? '';
            }
            $rows[] = $row;
        }
        fclose($fh);
        return $rows;
    }
}
