<?php

declare(strict_types=1);

namespace Rector\Jack\DependencyInjection;

use Entropy\Container\Container;

final class ContainerFactory
{
    public function create(): Container
    {
        $container = new Container();
        $container->autodiscover(__DIR__ . '/../../src');

        return $container;
    }
}
