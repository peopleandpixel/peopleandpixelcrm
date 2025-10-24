#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Container;

$container = new Container();
/** @var App\Service\BackupService $svc */
$svc = $container->get('backupService');

$cmd = $argv[1] ?? 'create';
try {
    switch ($cmd) {
        case 'create':
            $path = $svc->createSnapshot();
            echo "Created snapshot: " . $path . PHP_EOL;
            break;
        case 'list':
            $list = $svc->listSnapshots();
            foreach ($list as $s) {
                echo $s['file'] . "\t" . $s['created_at'] . "\t" . $s['size'] . " bytes" . PHP_EOL;
            }
            break;
        case 'verify':
            $file = $argv[2] ?? '';
            if ($file === '') { fwrite(STDERR, "Usage: backup.php verify <file>\n"); exit(1); }
            $res = $svc->verifySnapshot($file);
            if ($res['ok']) { echo "OK\n"; } else { echo "FAIL: " . implode('; ', $res['errors']) . "\n"; exit(2); }
            break;
        case 'restore':
            $file = $argv[2] ?? '';
            if ($file === '') { fwrite(STDERR, "Usage: backup.php restore <file>\n"); exit(1); }
            $svc->restoreSnapshot($file);
            echo "Restore completed.\n";
            break;
        default:
            echo "Usage: backup.php [create|list|verify <file>|restore <file>]\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
