<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
    )
    ->withCodeQualityLevel(70)
    ->withImportNames()
    ->withTreatClassesAsFinal();
