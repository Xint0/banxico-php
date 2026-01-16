<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(22)
    ->withDeadCodeLevel(22)
    ->withCodeQualityLevel(22)
    ->withImportNames()
    ->withTreatClassesAsFinal();
