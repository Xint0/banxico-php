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
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
    )
    ->withDeadCodeLevel(63)
    ->withCodeQualityLevel(63)
    ->withImportNames()
    ->withTreatClassesAsFinal();
