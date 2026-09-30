<?php

declare(strict_types=1);

namespace Rector\Jack\DependencyInjection;

use Entropy\Container\Container;
use Rector\Jack\Command\ListCommand;
use Rector\Jack\Composer\InstalledVersionResolver;

final class ContainerFactory
{
    public function create(): Container
    {
        $container = new Container();

        // register with container itself, so the help printer can be resolved lazily without circular dependency
        $container->service(
            ListCommand::class,
            static fn(Container $container): ListCommand => new ListCommand($container)
        );

        // resolve the installed.json from the current working directory of the analyzed project
        $container->service(
            InstalledVersionResolver::class,
            static fn(): InstalledVersionResolver => new InstalledVersionResolver(
                getcwd() . '/vendor/composer/installed.json'
            )
        );

        $container->autodiscover(__DIR__ . '/../../src');

        return $container;
    }
}
