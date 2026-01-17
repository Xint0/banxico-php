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
        codeQuality: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
    )
    ->withCodingStyleLevel(1)
    ->withImportNames()
    ->withTreatClassesAsFinal();
