<?php

declare(strict_types=1);

namespace Rector\Jack\Command;

use Nette\Utils\Json;
use Rector\Jack\Composer\ComposerOutdatedResponseProvider;
use Rector\Jack\Enum\ComposerKey;
use Rector\Jack\OutdatedComposerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class BreakPointCommand extends Command
{
    public function __construct(
        private readonly OutdatedComposerFactory $outdatedComposerFactory,
        private readonly ComposerOutdatedResponseProvider $composerOutdatedResponseProvider
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('breakpoint');

        $this->setDescription('Let your CI tell you, if there is too many major-version outdated packages');
        $this->addOption('dev', null, InputOption::VALUE_NONE, 'Focus on dev packages only');

        $this->addOption(
            'limit',
            null,
            InputOption::VALUE_REQUIRED,
            'Maximum number of outdated major version packages',
            5
        );

        $this->addOption(
            'ignore',
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
            'Ignore packages by name, e.g. "symfony/" or "symfony/console"',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $maxOutdatePackages = (int) $input->getOption('limit');
        $onlyDev = (bool) $input->getOption('dev');

        $symfonyStyle->writeln('<fg=green>Analyzing "composer.json" for major outdated packages</>');

        $responseJsonContents = $this->composerOutdatedResponseProvider->provide();

        $responseJson = Json::decode($responseJsonContents, true);
        if (! isset($responseJson[ComposerKey::INSTALLED_KEY])) {
            $symfonyStyle->success('All packages are up to date');

            return self::SUCCESS;
        }

        $composerJsonFilePath = getcwd() . '/composer.json';
        $outdatedComposer = $this->outdatedComposerFactory->createOutdatedComposer(
            array_filter(
                $responseJson[ComposerKey::INSTALLED_KEY],
                static function (array $package) use ($input): bool {
                    foreach ($input->getOption('ignore') as $ignoredPackage) {
                        if (str_contains((string) $package['name'], $ignoredPackage)) {
                            return false;
                        }
                    }

                    return true;
                }
            ),
            $composerJsonFilePath
        );

        if ($outdatedComposer->count() === 0) {
            $symfonyStyle->success('All packages are up to date');
            return self::SUCCESS;
        }

        $symfonyStyle->title(
            sprintf(
                'Found %d outdated package%s',
                $outdatedComposer->count($onlyDev),
                $outdatedComposer->count($onlyDev) > 1 ? 's' : ''
            )
        );

        foreach ($outdatedComposer->getPackages($onlyDev) as $outdatedPackage) {
            $symfonyStyle->writeln(
                sprintf('The "<fg=green>%s</>" package is outdated', $outdatedPackage->getName())
            );

            if ($outdatedPackage->getCurrentVersionAge()) {
                $symfonyStyle->writeln(sprintf(
                    ' * Your version %s is <fg=%s>%s</>',
                    $outdatedPackage->getCurrentVersion(),
                    $outdatedPackage->isVeryOld() ? 'red' : 'yellow',
                    $outdatedPackage->getCurrentVersionAge(),
                ));
            } else {
                // composer 2.7- compatible
                $symfonyStyle->writeln(sprintf(' * Your version is %s', $outdatedPackage->getCurrentVersion()));
            }

            $symfonyStyle->writeln(sprintf(' * Bump to %s', $outdatedPackage->getLatestVersion()));
            $symfonyStyle->newLine();
        }

        $symfonyStyle->newLine();
        if ($outdatedComposer->count() >= $maxOutdatePackages) {
            // to much → fail
            $symfonyStyle->error(sprintf(
                'There %s %d outdated package%s. Update couple of them to get under %d limit',
                $outdatedComposer->count() > 1 ? 'are' : 'is',
                $outdatedComposer->count(),
                $outdatedComposer->count() > 1 ? 's' : '',
                $maxOutdatePackages
            ));

            return self::FAILURE;
        }

        if ($outdatedComposer->count() > max(1, $maxOutdatePackages - 5)) {
            // to much → fail
            $symfonyStyle->warning(sprintf(
                'There are %d outdated packages. Soon, the count will go over %d limit and this job will fail.%sUpgrade in time',
                $outdatedComposer->count(),
                $maxOutdatePackages,
                PHP_EOL
            ));

            return self::SUCCESS;
        }

        // to many → fail
        $symfonyStyle->success(
            sprintf('Still far away from limit %d. Good job keeping your project up to date!', $maxOutdatePackages)
        );

        return self::SUCCESS;
    }
}
