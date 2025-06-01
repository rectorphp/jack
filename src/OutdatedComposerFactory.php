<?php

declare(strict_types=1);

namespace Rector\Jack;

use Rector\Jack\Mapper\OutdatedPackageMapper;
use Rector\Jack\ValueObject\OutdatedComposer;
use Rector\Jack\ValueObject\OutdatedPackage;

/**
 * @see \Rector\Jack\Tests\OutdatedComposerFactory\OutdatedComposerFactoryTest
 */
final readonly class OutdatedComposerFactory
{
    public function __construct(
        private OutdatedPackageMapper $outdatedPackageMapper
    ) {
    }

    /**
     * @param mixed[] $installedPackages
     */
    public function createOutdatedComposer(array $installedPackages, string $composerJsonFilePath): OutdatedComposer
    {
        $outdatedPackages = $this->outdatedPackageMapper->mapToObjects($installedPackages, $composerJsonFilePath);

        // filter out dev packages, those are silently added, when "minimum-stability" is set to "dev"
        $nonDevOutdatedPackages = array_filter(
            $outdatedPackages,
            fn (OutdatedPackage $outdatedPackage): bool => ! $outdatedPackage->lastestIsDevBranch()
        );

        return new OutdatedComposer($nonDevOutdatedPackages);
    }
}
