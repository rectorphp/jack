<?php

declare (strict_types=1);
namespace Rector\Jack\ValueObject;

final class OutdatedComposer
{
    /**
     * @var OutdatedPackage[]
     * @readonly
     */
    private $outdatedPackages;
    /**
     * @param OutdatedPackage[] $outdatedPackages
     */
    public function __construct(array $outdatedPackages)
    {
        $this->outdatedPackages = $outdatedPackages;
    }
    public function getProdPackagesCount() : int
    {
        return \count($this->getProdPackages());
    }
    public function getDevPackagesCount() : int
    {
        return \count($this->getDevPackages());
    }
    /**
     * @return OutdatedPackage[]
     */
    public function getProdPackages() : array
    {
        return \array_filter($this->outdatedPackages, function (\Rector\Jack\ValueObject\OutdatedPackage $outdatedPackage) : bool {
            return $outdatedPackage->isProd();
        });
    }
    /**
     * @return OutdatedPackage[]
     */
    public function getDevPackages() : array
    {
        return \array_filter($this->outdatedPackages, function (\Rector\Jack\ValueObject\OutdatedPackage $outdatedPackage) : bool {
            return !$outdatedPackage->isProd();
        });
    }
    public function count() : int
    {
        return \count($this->outdatedPackages);
    }
    /**
     * @return OutdatedPackage[]
     */
    public function getPackages() : array
    {
        return $this->outdatedPackages;
    }
    /**
     * @return OutdatedPackage[]
     */
    public function getPackagesShuffled(bool $onlyDev = \false) : array
    {
        // adds random effect, not to always update by A-Z, as would force too narrow pattern
        // this is also more fun :)
        if ($onlyDev) {
            $outdatedPackages = $this->getDevPackages();
        } else {
            $outdatedPackages = $this->outdatedPackages;
        }
        \shuffle($outdatedPackages);
        return $outdatedPackages;
    }
}
