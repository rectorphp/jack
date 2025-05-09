<?php

declare(strict_types=1);

namespace Rector\Jack\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rector\Jack\Composer\ComposerOutdatedResponseProvider;
use Rector\Jack\Composer\NextVersionResolver;
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
        private readonly NextVersionResolver $nextVersionResolver,
        private readonly OutdatedComposerFactory $outdatedComposerFactory,
        private readonly ComposerOutdatedResponseProvider $composerOutdatedResponseProvider,
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

        $outdatedPackages = $outdatedComposer->getPackagesShuffled($onlyDev, $packagePrefix);

        $openedPackageCount = 0;
        foreach ($outdatedPackages as $outdatedPackage) {
            $composerVersion = $outdatedPackage->getComposerVersion();

            // already filled with open version
            if (str_contains($composerVersion, '|')) {
                continue;
            }

            // convert composer version to next version
            $nextVersion = $this->nextVersionResolver->resolve($outdatedPackage->getName(), $composerVersion);
            $openedVersion = $composerVersion . '|' . $nextVersion;

            // replace using regex, to keep original composer.json format
            $composerJsonContents = Strings::replace(
                $composerJsonContents,
                // find
                sprintf('#"%s": "(.*?)"#', $outdatedPackage->getName()),
                // replace
                sprintf('"%s": "%s"', $outdatedPackage->getName(), $openedVersion)
            );

            $symfonyStyle->writeln(sprintf(
                ' * Opened "<fg=green>%s</>" package to "<fg=yellow>%s</>" version',
                $outdatedPackage->getName(),
                $openedVersion
            ));

            ++$openedPackageCount;
            if ($openedPackageCount >= $limit) {
                // we've reached the limit, so we can stop
                break;
            }
        }

        if ($isDryRun === false) {
            // update composer.json file, only if no --dry-run
            FileSystem::write($composerJsonFilePath, $composerJsonContents . PHP_EOL);
        }

        $symfonyStyle->success(
            sprintf(
                '%d packages %s opened up to the next nearest version.%s%s "composer update" to push versions up',
                $openedPackageCount,
                $isDryRun ? 'would be (is "--dry-run")' : 'were',
                PHP_EOL,
                $isDryRun ? 'Then you would run' : 'Now run'
            )
        );

        return self::SUCCESS;
    }
}
