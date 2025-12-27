<?php

// @see https://github.com/shipmonk-rnd/composer-dependency-analyser/
declare (strict_types=1);
namespace Jack202512;

use Jack202512\ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use Jack202512\ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;
return (new Configuration())->ignoreErrorsOnExtension('ext-filter', [ErrorType::SHADOW_DEPENDENCY]);
