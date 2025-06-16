<?php

declare (strict_types=1);
namespace Rector\Jack\Command;

use Jack202506\Nette\Utils\FileSystem;
use Rector\Jack\ComposerProcessor\RaiseToInstalledComposerProcessor;
use Jack202506\Symfony\Component\Console\Command\Command;
use Jack202506\Symfony\Component\Console\Input\InputInterface;
use Jack202506\Symfony\Component\Console\Input\InputOption;
use Jack202506\Symfony\Component\Console\Output\OutputInterface;
use Jack202506\Symfony\Component\Console\Style\SymfonyStyle;
use Jack202506\Webmozart\Assert\Assert;
final class RaiseToInstalledCommand extends Command
{
    /**
     * @readonly
     * @var \Rector\Jack\ComposerProcessor\RaiseToInstalledComposerProcessor
     */
    private $raiseToInstalledComposerProcessor;
    public function __construct(RaiseToInstalledComposerProcessor $raiseToInstalledComposerProcessor)
    {
        $this->raiseToInstalledComposerProcessor = $raiseToInstalledComposerProcessor;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('raise-to-installed');
        $this->setDescription('Raise your version in "composer.json" to installed one to get the latest version available in any composer update');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only show diff of "composer.json" changes, do not write the file');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $isDryRun = (bool) $input->getOption('dry-run');
        $symfonyStyle->writeln('<fg=green>Analyzing "/vendor/composer/installed.json" for versions</>');
        // load composer.json and replace versions in "require" and "require-dev",
        $composerJsonFilePath = \getcwd() . '/composer.json';
        Assert::fileExists($composerJsonFilePath);
        $composerJsonContents = FileSystem::read($composerJsonFilePath);
        $changedPackageVersionsResult = $this->raiseToInstalledComposerProcessor->process($composerJsonContents);
        $changedPackages = $changedPackageVersionsResult->getChangedPackageVersions();
        if ($changedPackages === []) {
            $symfonyStyle->success('No changes made to "composer.json"');
            return self::SUCCESS;
        }
        if ($isDryRun === \false) {
            $changedComposerJsonContents = $changedPackageVersionsResult->getComposerJsonContents();
            FileSystem::write($composerJsonFilePath, \rtrim($changedComposerJsonContents) . \PHP_EOL, null);
        }
        $symfonyStyle->success(\sprintf('%d package%s %s changed to installed versions.%s%s "composer update --lock" to update "composer.lock" hash', \count($changedPackages), \count($changedPackages) === 1 ? '' : 's', $isDryRun ? 'would be (is "--dry-run")' : 'were updated', \PHP_EOL, $isDryRun ? 'Then you would run' : 'Now run'));
        foreach ($changedPackages as $changedPackage) {
            $symfonyStyle->writeln(\sprintf(' * <fg=green>%s</> (<fg=yellow>%s</> => <fg=yellow>%s</>)', $changedPackage->getPackageName(), $changedPackage->getOldVersion(), $changedPackage->getNewVersion()));
        }
        $symfonyStyle->newLine();
        return self::SUCCESS;
    }
}
