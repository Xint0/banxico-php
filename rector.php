<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(21)
    ->withDeadCodeLevel(21)
    ->withCodeQualityLevel(21)
    ->withImportNames()
    ->withTreatClassesAsFinal();
