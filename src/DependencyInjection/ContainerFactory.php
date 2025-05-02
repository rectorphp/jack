<?php

declare (strict_types=1);
namespace Rector\Jack\DependencyInjection;

use Jack202505\Illuminate\Container\Container;
use Jack202505\Symfony\Component\Console\Application;
use Jack202505\Symfony\Component\Console\Input\ArrayInput;
use Jack202505\Symfony\Component\Console\Output\ConsoleOutput;
use Jack202505\Symfony\Component\Console\Style\SymfonyStyle;
use Jack202505\Symfony\Component\Finder\Finder;
use Jack202505\Webmozart\Assert\Assert;
final class ContainerFactory
{
    /**
     * @api used in bin and tests
     */
    public function create() : Container
    {
        $container = new Container();
        // console
        $container->singleton(Application::class, function (Container $container) : Application {
            $application = new Application('Rector Jack');
            $commandClasses = $this->findCommandClasses();
            // register commands
            foreach ($commandClasses as $commandClass) {
                $command = $container->make($commandClass);
                $application->add($command);
            }
            // remove basic command to make output clear
            $this->hideDefaultCommands($application);
            return $application;
        });
        $container->singleton(SymfonyStyle::class, static function () : SymfonyStyle {
            return new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
        });
        return $container;
    }
    public function hideDefaultCommands(Application $application) : void
    {
        $application->get('list')->setHidden(\true);
        $application->get('completion')->setHidden(\true);
        $application->get('help')->setHidden(\true);
    }
    /**
     * @return string[]
     */
    private function findCommandClasses() : array
    {
        $commandFinder = Finder::create()->files()->name('*Command.php')->in(__DIR__ . '/../Command');
        $commandClasses = [];
        foreach ($commandFinder as $commandFile) {
            $commandClass = 'Rector\\Jack\\Command\\' . $commandFile->getBasename('.php');
            // make sure it exists
            Assert::classExists($commandClass);
            $commandClasses[] = $commandClass;
        }
        return $commandClasses;
    }
}
