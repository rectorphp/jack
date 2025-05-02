<?php

declare (strict_types=1);
namespace Rector\Jack;

use Rector\Jack\Mapper\OutdatedPackageMapper;
use Rector\Jack\ValueObject\OutdatedComposer;
final class OutdatedComposerFactory
{
    /**
     * @readonly
     * @var \Rector\Jack\Mapper\OutdatedPackageMapper
     */
    private $outdatedPackageMapper;
    public function __construct(OutdatedPackageMapper $outdatedPackageMapper)
    {
        $this->outdatedPackageMapper = $outdatedPackageMapper;
    }
    /**
     * @param mixed[] $installedPackages
     */
    public function createOutdatedComposer(array $installedPackages, string $composerJsonFilePath) : OutdatedComposer
    {
        $outdatedPackages = $this->outdatedPackageMapper->mapToObjects($installedPackages, $composerJsonFilePath);
        return new OutdatedComposer($outdatedPackages);
    }
}
