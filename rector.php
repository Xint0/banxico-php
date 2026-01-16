<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(14)
    ->withDeadCodeLevel(14)
    ->withCodeQualityLevel(14)
    ->withImportNames()
    ->withTreatClassesAsFinal();
