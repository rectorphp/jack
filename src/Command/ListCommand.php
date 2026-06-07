<?php

declare(strict_types=1);

namespace Rector\Jack\Command;

use Entropy\Console\Contract\CommandInterface;
use Entropy\Console\Enum\ExitCode;
use Entropy\Console\Output\HelpPrinter;
use Entropy\Container\Container;

final readonly class ListCommand implements CommandInterface
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function run(): int
    {
        // resolved lazily to avoid circular dependency: ListCommand → HelpPrinter → CommandRegistry → ListCommand
        $helpPrinter = $this->container->make(HelpPrinter::class);
        $helpPrinter->print();

        return ExitCode::SUCCESS;
    }

    public function getName(): string
    {
        return 'list';
    }

    public function getDescription(): string
    {
        return 'List available commands';
    }
}
