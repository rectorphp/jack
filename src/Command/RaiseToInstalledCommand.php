<?php

declare (strict_types=1);
namespace Rector\Jack\Command;

use Jack202604\Entropy\Console\Contract\CommandInterface;
use Jack202604\Entropy\Console\Enum\ExitCode;
use Jack202604\Entropy\Console\Output\OutputPrinter;
use Jack202604\Nette\Utils\FileSystem;
use Rector\Jack\ComposerProcessor\RaiseToInstalledComposerProcessor;
use Webmozart\Assert\Assert;
final class RaiseToInstalledCommand implements CommandInterface
{
    /**
     * @readonly
     * @var \Rector\Jack\ComposerProcessor\RaiseToInstalledComposerProcessor
     */
    private $raiseToInstalledComposerProcessor;
    /**
     * @readonly
     * @var \Entropy\Console\Output\OutputPrinter
     */
    private $outputPrinter;
    public function __construct(RaiseToInstalledComposerProcessor $raiseToInstalledComposerProcessor, OutputPrinter $outputPrinter)
    {
        $this->raiseToInstalledComposerProcessor = $raiseToInstalledComposerProcessor;
        $this->outputPrinter = $outputPrinter;
    }
    /**
     * @return ExitCode::*
     */
    public function run(bool $dryRun = \false) : int
    {
        $this->outputPrinter->green('Analyzing "/vendor/composer/installed.json" for versions');
        // load composer.json and replace versions in "require" and "require-dev",
        $composerJsonFilePath = \getcwd() . '/composer.json';
        Assert::fileExists($composerJsonFilePath);
        $composerJsonContents = FileSystem::read($composerJsonFilePath);
        $changedPackageVersionsResult = $this->raiseToInstalledComposerProcessor->process($composerJsonContents);
        $changedPackages = $changedPackageVersionsResult->getChangedPackageVersions();
        if ($changedPackages === []) {
            $this->outputPrinter->greenBackground('No changes made to "composer.json"');
            return ExitCode::SUCCESS;
        }
        if ($dryRun === \false) {
            $changedComposerJsonContents = $changedPackageVersionsResult->getComposerJsonContents();
            FileSystem::write($composerJsonFilePath, \rtrim($changedComposerJsonContents) . \PHP_EOL, null);
        }
        $this->outputPrinter->greenBackground(\sprintf('%d package%s %s changed to installed versions.%s%s "composer update --lock" to update "composer.lock" hash', \count($changedPackages), \count($changedPackages) === 1 ? '' : 's', $dryRun ? 'would be (is "--dry-run")' : 'were updated', \PHP_EOL, $dryRun ? 'Then you would run' : 'Now run'));
        foreach ($changedPackages as $changedPackage) {
            $this->outputPrinter->writeln(\sprintf(' * <fg=green>%s</> (<fg=yellow>%s</> => <fg=yellow>%s</>)', $changedPackage->getPackageName(), $changedPackage->getOldVersion(), $changedPackage->getNewVersion()));
        }
        $this->outputPrinter->newLine();
        return ExitCode::SUCCESS;
    }
    public function getName() : string
    {
        return 'raise-to-installed';
    }
    public function getDescription() : string
    {
        return 'Raise your version in "composer.json" to installed one to get the latest version available in any composer update';
    }
}
