<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPreparedSets(psr12: true, common: true, perCs: true)
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withRootFiles();
