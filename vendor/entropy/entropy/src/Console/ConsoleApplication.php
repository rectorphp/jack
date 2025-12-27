<?php

declare (strict_types=1);
namespace Jack202512\Entropy\Console;

use Jack202512\Entropy\Attributes\RelatedTest;
use Jack202512\Entropy\Console\Enum\ExitCode;
use Jack202512\Entropy\Console\Input\InputParser;
use Jack202512\Entropy\Console\Mapper\CLIRequestMapper;
use Jack202512\Entropy\Console\Output\CommandHelpFactory;
use Jack202512\Entropy\Console\Output\HelpPrinter;
use Jack202512\Entropy\Console\Output\OutputPrinter;
use Jack202512\Entropy\Tests\Console\ConsoleApplication\ConsoleApplicationTest;
final class ConsoleApplication
{
    /**
     * @readonly
     * @var \Entropy\Console\Output\HelpPrinter
     */
    private $helpPrinter;
    /**
     * @readonly
     * @var \Entropy\Console\Output\OutputPrinter
     */
    private $outputPrinter;
    /**
     * @readonly
     * @var \Entropy\Console\Output\CommandHelpFactory
     */
    private $commandHelpFactory;
    /**
     * @readonly
     * @var \Entropy\Console\Input\InputParser
     */
    private $inputParser;
    /**
     * @readonly
     * @var \Entropy\Console\CommandRegistry
     */
    private $commandRegistry;
    /**
     * @readonly
     * @var \Entropy\Console\Mapper\CLIRequestMapper
     */
    private $cliRequestMapper;
    public function __construct(HelpPrinter $helpPrinter, OutputPrinter $outputPrinter, CommandHelpFactory $commandHelpFactory, InputParser $inputParser, CommandRegistry $commandRegistry, CLIRequestMapper $cliRequestMapper)
    {
        $this->helpPrinter = $helpPrinter;
        $this->outputPrinter = $outputPrinter;
        $this->commandHelpFactory = $commandHelpFactory;
        $this->inputParser = $inputParser;
        $this->commandRegistry = $commandRegistry;
        $this->cliRequestMapper = $cliRequestMapper;
    }
    /**
     * @param mixed[] $argv
     * @return ExitCode::*
     */
    public function run(array $argv) : int
    {
        $cliRequest = $this->inputParser->parse($argv);
        // global help
        if ($cliRequest->isHelp()) {
            $this->helpPrinter->print();
            return ExitCode::SUCCESS;
        }
        /** @var string $commandName */
        $commandName = $cliRequest->getCommandName();
        if (!$this->commandRegistry->has($commandName)) {
            \fwrite(\STDERR, \sprintf("Unknown command: %s\n\n", $commandName));
            $this->helpPrinter->print();
            return ExitCode::INVALID_COMMAND;
        }
        try {
            $command = $this->commandRegistry->get($commandName);
            if ($cliRequest->isCommandHelp()) {
                // build command help here :)
                $commandHelp = $this->commandHelpFactory->build($command);
                $this->outputPrinter->writeln($commandHelp);
                return ExitCode::SUCCESS;
            }
            $runArguments = $this->cliRequestMapper->resolveArguments($command, $cliRequest);
            return $command->run(...$runArguments);
        } catch (\Throwable $throwable) {
            \fwrite(\STDERR, "Unhandled error: {$throwable->getMessage()}\n");
            return ExitCode::ERROR;
        }
    }
}
