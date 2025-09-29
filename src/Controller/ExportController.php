<?php

declare(strict_types=1);

namespace App\Controller;

use App\Container;
use function render;

class ExportController
{
    /**
     * Export any supported entity to JSON.
     * Path: /export/{entity}.json
     */
    public static function json(Container $container, string $entity): void
    {
        $entity = self::normalizeEntity($entity);
        $store = self::resolveStore($container, $entity);
        if ($store === null) {
            http_response_code(404);
            render('errors/404', ['path' => "/export/$entity.json", 'method' => 'GET']);
            return;
        }
        $items = $store->all();
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $entity . '.json"');
        echo json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Export any supported entity to CSV.
     * Path: /export/{entity}.csv
     */
    public static function csv(Container $container, string $entity): void
    {
        $entity = self::normalizeEntity($entity);
        $store = self::resolveStore($container, $entity);
        if ($store === null) {
            http_response_code(404);
            render('errors/404', ['path' => "/export/$entity.csv", 'method' => 'GET']);
            return;
        }
        $items = $store->all();
        // Build headers from union of keys, keep stable order with id first
        $headers = [];
        foreach ($items as $row) {
            foreach (array_keys($row) as $k) { $headers[$k] = true; }
        }
        $headers = array_keys($headers);
        usort($headers, function($a, $b) {
            if ($a === 'id') return -1; if ($b === 'id') return 1; return strcmp($a, $b);
        });
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $entity . '.csv"');
        $out = fopen('php://output', 'w');
        if (!empty($headers)) { fputcsv($out, $headers); }
        foreach ($items as $row) {
            $line = [];
            foreach ($headers as $h) { $line[] = $row[$h] ?? ''; }
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }

    private static function normalizeEntity(string $entity): string
    {
        return strtolower(trim($entity));
    }

    private static function resolveStore(Container $container, string $entity): ?object
    {
        $supported = ['contacts','times','tasks','employees','candidates','payments','storage','storage_adjustments'];
        if (!in_array($entity, $supported, true)) {
            return null;
        }
        $id = $entity . 'Store';
        try {
            return $container->get($id);
        } catch (\Throwable) {
            return null;
        }
    }
}
