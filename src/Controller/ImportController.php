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
        $dryRun = isset($_POST['dry_run']) && ($_POST['dry_run'] === '1' || $_POST['dry_run'] === 'on');
        $payload = isset($_POST['payload']) ? (string)$_POST['payload'] : '';

        $errors = [];
        if ($entity === '' || !in_array($entity, self::supportedEntities(), true)) {
            $errors[] = 'Unsupported or missing entity.';
        }
        if ($payload === '') {
            $errors[] = 'No data provided.';
        }
        if (!empty($errors)) {
            render('import_form', ['entities' => self::supportedEntities(), 'error' => implode(' ', $errors), 'old' => compact('entity','format','payload','dryRun')]);
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
            render('import_form', ['entities' => self::supportedEntities(), 'error' => 'Failed to parse input: ' . $e->getMessage(), 'old' => compact('entity','format','payload','dryRun')]);
            exit;
        }

        // Normalize: expect list of associative rows
        $items = [];
        foreach ($rows as $r) { if (is_array($r)) { $items[] = $r; } }

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

        $imported = 0;
        if (!$dryRun && empty($invalid)) {
            $store = $container->get($storeId);
            // Strategy: add one by one to keep ids consistent
            foreach ($valid as $row) {
                $store->add($row);
                $imported++;
            }
            Flash::success('Import completed: ' . $imported . ' items imported.');
            redirect('/');
        }

        render('import_result', [
            'entity' => $entity,
            'format' => $format,
            'dry_run' => $dryRun,
            'total' => count($items),
            'valid_count' => count($valid),
            'invalid_count' => count($invalid),
            'invalid' => $invalid,
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
