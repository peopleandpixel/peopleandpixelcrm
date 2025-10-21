<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // Include baseline if present
    $baseline = __DIR__ . '/rector-baseline.php';
    if (file_exists($baseline)) {
        $rectorConfig->import($baseline);
    }

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_84,
        SetList::TYPE_DECLARATION,
        SetList::CODE_QUALITY,
        SetList::EARLY_RETURN,
        SetList::DEAD_CODE,
    ]);

    $rectorConfig->cacheDirectory(__DIR__ . '/var/cache/rector');
};
