<?php

declare(strict_types=1);

namespace Rector\Jack\DependencyInjection;

use Entropy\Container\Container;
use Rector\Jack\Command\ListCommand;

final class ContainerFactory
{
    public function create(): Container
    {
        $container = new Container();

        // register with container itself, so the help printer can be resolved lazily without circular dependency
        $container->service(
            ListCommand::class,
            static fn (Container $container): ListCommand => new ListCommand($container)
        );

        $container->autodiscover(__DIR__ . '/../../src');

        return $container;
    }
}
