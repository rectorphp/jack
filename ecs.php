<?php

declare(strict_types=1);

use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPreparedSets(psr12: true, common: true, symplify: true)
    ->withRules([LineLengthFixer::class])
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withRootFiles();
