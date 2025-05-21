<?php

declare(strict_types=1);

namespace Rector\Jack\ComposerProcessor;

use Rector\Jack\Composer\NextVersionResolver;
use Rector\Jack\FileSystem\ComposerJsonPackageVersionUpdater;
use Rector\Jack\ValueObject\ChangedPackageVersion;
use Rector\Jack\ValueObject\ComposerProcessorResult\ChangedPackageVersionsResult;
use Rector\Jack\ValueObject\OutdatedComposer;

/**
 * @see \Rector\Jack\Tests\ComposerProcessor\OpenVersionsComposerProcessor\OpenVersionsComposerProcessorTest
 */
final readonly class OpenVersionsComposerProcessor
{
    public function __construct(
        private NextVersionResolver $nextVersionResolver
    ) {
    }

    public function process(
        string $composerJsonContents,
        OutdatedComposer $outdatedComposer,
        int $limit,
        bool $onlyDev,
        ?string $packagePrefix
    ): ChangedPackageVersionsResult {
        $outdatedPackages = $outdatedComposer->getPackagesShuffled($onlyDev, $packagePrefix);

        $openedPackages = [];

        foreach ($outdatedPackages as $outdatedPackage) {
            $composerVersion = $outdatedPackage->getComposerVersion();

            if ($this->skipExistingVersion($composerVersion)) {
                continue;
            }

            // convert composer version to next version
            $nextVersion = $this->nextVersionResolver->resolve($outdatedPackage->getName(), $composerVersion);
            $openedVersion = $composerVersion . '|' . $nextVersion;

            // replace using regex, to keep original composer.json format
            $composerJsonContents = ComposerJsonPackageVersionUpdater::update(
                $composerJsonContents,
                $outdatedPackage->getName(),
                $openedVersion
            );

            $openedPackages[] = new ChangedPackageVersion(
                $outdatedPackage->getName(),
                $composerVersion,
                $openedVersion,
            );

            if (count($openedPackages) >= $limit) {
                // we've reached the limit, so we can stop
                break;
            }
        }

        return new ChangedPackageVersionsResult($composerJsonContents, $openedPackages);
    }

    private function skipExistingVersion(string $composerVersion): bool
    {
        // already filled with open version
        if (str_contains($composerVersion, '|')) {
            return true;
        }

        return str_contains($composerVersion, 'dev-');
    }
}
