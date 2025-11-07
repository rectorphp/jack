<?php

declare (strict_types=1);
namespace Rector\Jack\Console\Command;

use Jack202511\Symfony\Component\Console\Application;
use Jack202511\Symfony\Component\Console\Command\Command;
use Jack202511\Symfony\Component\Console\Command\ListCommand;
use Jack202511\Symfony\Component\Console\Descriptor\ApplicationDescription;
use Jack202511\Symfony\Component\Console\Input\InputInterface;
use Jack202511\Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;
/**
 * Simple command list, without bloated options
 */
final class CleanListCommand extends ListCommand
{
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        Assert::isInstanceOf($this->getApplication(), Application::class);
        $output->writeln($this->getApplication()->getName());
        $output->writeln('');
        $output->writeln('<comment>Available commands:</>');
        $applicationDescription = new ApplicationDescription($this->getApplication());
        $this->describeCommands($applicationDescription, $output);
        return self::SUCCESS;
    }
    /**
     * @param non-empty-array<Command> $commands
     */
    private function resolveCommandNameColumnWidth(array $commands) : int
    {
        $commandNameLengths = [];
        foreach ($commands as $command) {
            $commandNameLengths[] = \strlen((string) $command->getName());
        }
        return \max($commandNameLengths) + 4;
    }
    private function describeCommands(ApplicationDescription $applicationDescription, OutputInterface $output) : void
    {
        if ($applicationDescription->getCommands() === []) {
            return;
        }
        $commands = $applicationDescription->getCommands();
        $commandNameColumnWidth = $this->resolveCommandNameColumnWidth($commands);
        foreach ($commands as $command) {
            $spacingWidth = $commandNameColumnWidth - \strlen((string) $command->getName());
            $output->writeln(\sprintf('  <info>%s</>%s%s', $command->getName(), \str_repeat(' ', $spacingWidth), $command->getDescription()));
        }
    }
}
