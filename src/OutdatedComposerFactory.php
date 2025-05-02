<?php

declare(strict_types=1);

namespace Rector\Jack;

use Rector\Jack\Mapper\OutdatedPackageMapper;
use Rector\Jack\ValueObject\OutdatedComposer;

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

        return new OutdatedComposer($outdatedPackages);
    }
}
