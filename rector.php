<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(28)
    ->withDeadCodeLevel(28)
    ->withCodeQualityLevel(28)
    ->withImportNames()
    ->withTreatClassesAsFinal();
