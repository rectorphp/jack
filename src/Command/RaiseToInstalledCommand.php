<?php

declare(strict_types=1);

namespace Rector\Jack\Command;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Rector\Jack\FileSystem\ComposerJsonPackageVersionUpdater;
use Rector\Jack\Utils\JsonFileLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

final class RaiseToInstalledCommand extends Command
{
    public function __construct(
        private readonly VersionParser $versionParser
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('raise-to-lock');

        $this->setDescription(
            'Raise your version in "composer.json" to installed one to get the latest version available in any composer update'
        );

        // @todo add dry-run mode
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $symfonyStyle->writeln('<fg=green>Analyzing "/vendor/composer/installed.json" for versions</>');

        $installedPackagesToVersions = $this->resolveInstalledPackagesToVersions();

        // load composer.json and replace versions in "require" and "require-dev",
        $composerJsonFilePath = getcwd() . '/composer.json';

        Assert::fileExists($composerJsonFilePath);
        $composerJsonContents = FileSystem::read($composerJsonFilePath);
        $composerJson = Json::decode($composerJsonContents, true);

        $hasChanged = false;

        // iterate require and require-dev sections and check if installed version is newer one than in composer.json
        // if so, replace it
        foreach ($composerJson['require'] ?? [] as $packageName => $packageVersion) {
            if (! isset($installedPackagesToVersions[$packageName])) {
                continue;
            }

            $installedVersion = $installedPackagesToVersions[$packageName];

            // special case for unions
            if (str_contains((string) $packageVersion, '|')) {
                $passingVersionKeys = [];

                $unionPackageVersions = explode('|', (string) $packageVersion);
                foreach ($unionPackageVersions as $key => $unionPackageVersion) {
                    $unionPackageConstraint = $this->versionParser->parseConstraints($unionPackageVersion);

                    if (Comparator::greaterThanOrEqualTo(
                        $installedVersion,
                        $unionPackageConstraint->getLowerBound()
                            ->getVersion()
                    )) {
                        $passingVersionKeys[] = $key;
                    }
                }

                // nothing we can do, as lower union version is passing
                if ($passingVersionKeys === [0]) {
                    continue;
                }

                // higher version is meet, let's drop the lower one
                if ($passingVersionKeys === [0, 1]) {
                    $newPackageVersion = $unionPackageVersions[1];

                    $composerJsonContents = ComposerJsonPackageVersionUpdater::update(
                        $composerJsonContents,
                        $packageName,
                        $newPackageVersion
                    );

                    $hasChanged = true;
                    continue;
                }
            }

            $normalizedInstalledVersion = $this->versionParser->normalize($installedVersion);
            $installedPackageConstraint = $this->versionParser->parseConstraints($packageVersion);

            $normalizedConstraintVersion = $this->versionParser->normalize(
                $installedPackageConstraint->getLowerBound()
                    ->getVersion()
            );

            // remove "-dev" suffix
            $normalizedConstraintVersion = str_replace('-dev', '', $normalizedConstraintVersion);

            // all equal
            if ($normalizedConstraintVersion === $normalizedInstalledVersion) {
                continue;
            }

            [$major, $minor, $patch] = explode('.', $normalizedInstalledVersion);

            $newRequiredVersion = sprintf('^%s.%s', $major, $minor);

            // lets update
            $composerJsonContents = ComposerJsonPackageVersionUpdater::update(
                $composerJsonContents,
                $packageName,
                $newRequiredVersion
            );

            $hasChanged = true;
            continue;
            // focus on minor only
            // or on patch in case of 0.*
        }

        if ($hasChanged) {
            $symfonyStyle->success('Updating "composer.json" with installed versions');
            FileSystem::write($composerJsonFilePath, $composerJsonContents, null);
        } else {
            $symfonyStyle->success('No changes made to "composer.json"');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    private function resolveInstalledPackagesToVersions(): array
    {
        $installedJsonFilePath = getcwd() . '/vendor/composer/installed.json';

        $installedJson = JsonFileLoader::loadFileToJson($installedJsonFilePath);
        Assert::keyExists($installedJson, 'packages');

        $installedPackagesToVersions = [];
        foreach ($installedJson['packages'] as $installedPackage) {
            $packageName = $installedPackage['name'];
            $packageVersion = $installedPackage['version'];

            $installedPackagesToVersions[$packageName] = $packageVersion;
        }

        return $installedPackagesToVersions;
    }
}
