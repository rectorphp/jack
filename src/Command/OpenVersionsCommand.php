<?php

declare (strict_types=1);
namespace Rector\Jack\Command;

use Jack202505\Nette\Utils\FileSystem;
use Jack202505\Nette\Utils\Json;
use Jack202505\Nette\Utils\Strings;
use Rector\Jack\Composer\ComposerOutdatedResponseProvider;
use Rector\Jack\Composer\NextVersionResolver;
use Rector\Jack\Enum\ComposerKey;
use Rector\Jack\OutdatedComposerFactory;
use Jack202505\Symfony\Component\Console\Command\Command;
use Jack202505\Symfony\Component\Console\Input\InputInterface;
use Jack202505\Symfony\Component\Console\Input\InputOption;
use Jack202505\Symfony\Component\Console\Output\OutputInterface;
use Jack202505\Symfony\Component\Console\Style\SymfonyStyle;
final class OpenVersionsCommand extends Command
{
    /**
     * @readonly
     * @var \Rector\Jack\Composer\NextVersionResolver
     */
    private $nextVersionResolver;
    /**
     * @readonly
     * @var \Rector\Jack\OutdatedComposerFactory
     */
    private $outdatedComposerFactory;
    /**
     * @readonly
     * @var \Rector\Jack\Composer\ComposerOutdatedResponseProvider
     */
    private $composerOutdatedResponseProvider;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(NextVersionResolver $nextVersionResolver, OutdatedComposerFactory $outdatedComposerFactory, ComposerOutdatedResponseProvider $composerOutdatedResponseProvider, SymfonyStyle $symfonyStyle)
    {
        $this->nextVersionResolver = $nextVersionResolver;
        $this->outdatedComposerFactory = $outdatedComposerFactory;
        $this->composerOutdatedResponseProvider = $composerOutdatedResponseProvider;
        $this->symfonyStyle = $symfonyStyle;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('open-versions');
        $this->setDescription('Open composer.json version constraints to the very near next version');
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, 'How many packages to open-up', 5);
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Without any "composer.json" changes');
        $this->addOption('dev', null, InputOption::VALUE_NONE, 'Focus on dev packages only');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $composerJsonFilePath = \getcwd() . '/composer.json';
        $this->symfonyStyle->writeln('<fg=green>Analyzing "composer.json" for outdated packages</>');
        $responseJsonContents = $this->composerOutdatedResponseProvider->provide();
        $responseJson = Json::decode($responseJsonContents, \true);
        if (!isset($responseJson[ComposerKey::INSTALLED_KEY])) {
            $this->symfonyStyle->success('All packages are up to date. You are the best!');
            return self::SUCCESS;
        }
        $outdatedComposer = $this->outdatedComposerFactory->createOutdatedComposer($responseJson[ComposerKey::INSTALLED_KEY], $composerJsonFilePath);
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln(\sprintf('Found <fg=yellow>%d outdated package%s</>', $outdatedComposer->count(), $outdatedComposer->count() === 1 ? '' : 's'));
        $this->symfonyStyle->writeln(\sprintf(' * %d prod package%s', $outdatedComposer->getProdPackagesCount(), $outdatedComposer->getProdPackagesCount() === 1 ? '' : 's'));
        $this->symfonyStyle->writeln(\sprintf(' * %d dev package%s', $outdatedComposer->getDevPackagesCount(), $outdatedComposer->getDevPackagesCount() === 1 ? '' : 's'));
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->title('Opening version constraints in "composer.json"');
        $limit = (int) $input->getOption('limit');
        $isDryRun = (bool) $input->getOption('dry-run');
        $onlyDev = (bool) $input->getOption('dev');
        $composerJsonContents = FileSystem::read($composerJsonFilePath);
        $outdatedPackages = $outdatedComposer->getPackagesShuffled($onlyDev);
        $openedPackageCount = 0;
        foreach ($outdatedPackages as $outdatedPackage) {
            $composerVersion = $outdatedPackage->getComposerVersion();
            // already filled with open version
            if (\strpos($composerVersion, '|') !== \false) {
                continue;
            }
            // convert composer version to next version
            $nextVersion = $this->nextVersionResolver->resolve($composerVersion);
            $openedVersion = $composerVersion . '|' . $nextVersion;
            // replace using regex, to keep original composer.json format
            $composerJsonContents = Strings::replace($composerJsonContents, \sprintf('#"%s": "(.*?)"#', $outdatedPackage->getName()), $openedVersion);
            $this->symfonyStyle->writeln(\sprintf(' * Opened "<fg=green>%s</>" package to "<fg=yellow>%s</>" version', $outdatedPackage->getName(), $openedVersion));
            ++$openedPackageCount;
            if ($openedPackageCount >= $limit) {
                // we've reached the limit, so we can stop
                break;
            }
        }
        if ($isDryRun === \false) {
            // update composer.json file, only if no --dry-run
            FileSystem::write($composerJsonFilePath, $composerJsonContents . \PHP_EOL);
        }
        $this->symfonyStyle->success(\sprintf('%d packages %s opened up to the next nearest version.%s%s "composer update" to push versions up', $openedPackageCount, $isDryRun ? 'would be (is "--dry-run")' : 'were', \PHP_EOL, $isDryRun ? 'Then you would run' : 'Now run'));
        return self::SUCCESS;
    }
}
