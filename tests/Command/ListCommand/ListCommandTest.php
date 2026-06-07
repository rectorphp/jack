<?php

declare(strict_types=1);

namespace Rector\Jack\Tests\Command\ListCommand;

use Entropy\Console\Enum\ExitCode;
use Rector\Jack\Command\ListCommand;
use Rector\Jack\Tests\AbstractTestCase;

final class ListCommandTest extends AbstractTestCase
{
    private ListCommand $listCommand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listCommand = $this->make(ListCommand::class);
    }

    public function test(): void
    {
        $this->assertSame('list', $this->listCommand->getName());

        // output is silenced during unit tests, see OutputPrinter::isSilent
        $exitCode = $this->listCommand->run();
        $this->assertSame(ExitCode::SUCCESS, $exitCode);
    }
}
