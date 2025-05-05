<?php

declare(strict_types=1);

namespace Rector\Jack\ValueObject;

final readonly class OutdatedComposer
{
    /**
     * @param OutdatedPackage[] $outdatedPackages
     */
    public function __construct(
        private array $outdatedPackages
    ) {
    }

    public function getProdPackagesCount(): int
    {
        return count($this->getProdPackages());
    }

    public function getDevPackagesCount(): int
    {
        return count($this->getDevPackages());
    }

    /**
     * @return OutdatedPackage[]
     */
    public function getProdPackages(): array
    {
        return array_filter(
            $this->outdatedPackages,
            fn (OutdatedPackage $outdatedPackage): bool => $outdatedPackage->isProd()
        );
    }

    /**
     * @return OutdatedPackage[]
     */
    public function getDevPackages(): array
    {
        return array_filter(
            $this->outdatedPackages,
            fn (OutdatedPackage $outdatedPackage): bool => ! $outdatedPackage->isProd()
        );
    }

    public function count(bool $onlyDev = false): int
    {
        $packages = $onlyDev ? $this->getDevPackages() : $this->outdatedPackages;

        return count($packages);
    }

    /**
     * @return OutdatedPackage[]
     */
    public function getPackages(bool $onlyDev = false): array
    {
        return $onlyDev ? $this->getDevPackages() : $this->outdatedPackages;
    }

    /**
     * @return OutdatedPackage[]
     */
    public function getPackagesShuffled(bool $onlyDev = false): array
    {
        // adds random effect, not to always update by A-Z, as would force too narrow pattern
        // this is also more fun :)
        $outdatedPackages = $onlyDev ? $this->getDevPackages() : $this->outdatedPackages;

        shuffle($outdatedPackages);

        return $outdatedPackages;
    }
}
