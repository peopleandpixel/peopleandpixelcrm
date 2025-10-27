<?php
declare(strict_types=1);

// Reset local development data: clears var/* (except uploads if desired) and data/*.json to seed defaults, then runs seed-dev.php

$root = dirname(__DIR__);
$varDir = $root . '/var';
$dataDir = $root . '/data';
$keepUploads = in_array('--keep-uploads', $argv, true);

function rrmdir(string $dir, array $preserve = []): void {
    if (!is_dir($dir)) return;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        $path = $item->getPathname();
        foreach ($preserve as $keep) {
            if (str_starts_with($path, $keep)) {
                continue 2; // skip preserved
            }
        }
        if ($item->isDir()) {
            @rmdir($path);
        } else {
            @unlink($path);
        }
    }
}

// Clean var/*
$preserve = [];
$uploadsDir = $varDir . '/uploads';
if ($keepUploads && is_dir($uploadsDir)) { $preserve[] = realpath($uploadsDir) ?: $uploadsDir; }
rrmdir($varDir, $preserve);
@mkdir($varDir, 0775, true);
@mkdir($varDir . '/log', 0775, true);
@mkdir($varDir . '/cache', 0775, true);
if ($keepUploads) { @mkdir($uploadsDir, 0775, true); }

// Reset JSON data files if using file storage
$usingDb = getenv('USE_DB');
if (!$usingDb || $usingDb === '0' || strtolower((string)$usingDb) === 'false') {
    foreach (glob($dataDir.'/*.json') ?: [] as $file) {
        // Keep certain files if needed
        @unlink($file);
    }
}

// Run seeder
$seed = $root . '/bin/seed-dev.php';
if (is_file($seed)) {
    passthru(PHP_BINARY . ' ' . escapeshellarg($seed), $code);
    if ($code !== 0) {
        fwrite(STDERR, "Seeding failed with exit code $code\n");
        exit($code);
    }
    echo "Reset complete.\n";
} else {
    echo "Seed script not found, reset of var/ complete.\n";
}
