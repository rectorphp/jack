<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

// polyfill provides global functions, so no direct symbol usage is detected
return (new Configuration())
    ->ignoreErrorsOnPackage('symfony/polyfill-php80', [ErrorType::UNUSED_DEPENDENCY]);
