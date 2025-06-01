<?php

declare(strict_types=1);

namespace Rector\Jack\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Rector\Jack\Composer\ComposerOutdatedResponseProvider;
use Rector\Jack\ComposerProcessor\OpenVersionsComposerProcessor;
use Rector\Jack\Enum\ComposerKey;
use Rector\Jack\OutdatedComposerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class OpenVersionsCommand extends Command
{
    public function __construct(
        private readonly OutdatedComposerFactory $outdatedComposerFactory,
        private readonly ComposerOutdatedResponseProvider $composerOutdatedResponseProvider,
        private readonly OpenVersionsComposerProcessor $openVersionsComposerProcessor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('open-versions');

        $this->setDescription('Open composer.json version constraints to the very near next version');

        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, 'How many packages to open-up', 5);
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Without any "composer.json" changes');
        $this->addOption('dev', null, InputOption::VALUE_NONE, 'Focus on dev packages only');
        $this->addOption('package-prefix', null, InputOption::VALUE_REQUIRED, 'Name prefix to filter packages by');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $composerJsonFilePath = getcwd() . '/composer.json';

        $symfonyStyle->writeln('<fg=green>Analyzing "composer.json" for outdated packages</>');

        $responseJsonContents = $this->composerOutdatedResponseProvider->provide();

        $responseJson = Json::decode($responseJsonContents, true);
        if (! isset($responseJson[ComposerKey::INSTALLED_KEY])) {
            $symfonyStyle->success('All packages are up to date. You are the best!');

            return self::SUCCESS;
        }

        $outdatedComposer = $this->outdatedComposerFactory->createOutdatedComposer(
            $responseJson[ComposerKey::INSTALLED_KEY],
            $composerJsonFilePath
        );

        if ($outdatedComposer->count() === 0) {
            $symfonyStyle->success('All packages are up to date. You are the best!');

            return self::SUCCESS;
        }

        $symfonyStyle->newLine();

        $symfonyStyle->writeln(
            sprintf(
                'Found <fg=yellow>%d outdated package%s</>',
                $outdatedComposer->count(),
                $outdatedComposer->count() === 1 ? '' : 's'
            )
        );

        $symfonyStyle->writeln(sprintf(
            ' * %d prod package%s',
            $outdatedComposer->getProdPackagesCount(),
            $outdatedComposer->getProdPackagesCount() === 1 ? '' : 's'
        ));

        $symfonyStyle->writeln(sprintf(
            ' * %d dev package%s',
            $outdatedComposer->getDevPackagesCount(),
            $outdatedComposer->getDevPackagesCount() === 1 ? '' : 's'
        ));

        $symfonyStyle->newLine();
        $symfonyStyle->title('Opening version constraints in "composer.json"');

        $limit = (int) $input->getOption('limit');
        $isDryRun = (bool) $input->getOption('dry-run');
        $onlyDev = (bool) $input->getOption('dev');
        $packagePrefix = $input->getOption('package-prefix');

        $composerJsonContents = FileSystem::read($composerJsonFilePath);

        $changedPackageVersionsResult = $this->openVersionsComposerProcessor->process(
            $composerJsonContents,
            $outdatedComposer,
            $limit,
            $onlyDev,
            $packagePrefix
        );

        $openedPackages = $changedPackageVersionsResult->getChangedPackageVersions();
        $changedComposerJson = $changedPackageVersionsResult->getComposerJsonContents();

        if ($isDryRun === false) {
            // update composer.json file, only if no --dry-run
            FileSystem::write($composerJsonFilePath, $changedComposerJson . PHP_EOL, null);
        }

        $symfonyStyle->success(
            sprintf(
                '%d packages %s opened up to the next nearest version.%s%s "composer update" to push versions up',
                count($openedPackages),
                $isDryRun ? 'would be (is "--dry-run")' : 'were',
                PHP_EOL,
                $isDryRun ? 'Then you would run' : 'Now run'
            )
        );

        foreach ($openedPackages as $openedPackage) {
            $symfonyStyle->writeln(sprintf(
                ' * Opened "<fg=green>%s</>" package to "<fg=yellow>%s</>" version',
                $openedPackage->getPackageName(),
                $openedPackage->getNewVersion()
            ));
        }

        return self::SUCCESS;
    }
}
