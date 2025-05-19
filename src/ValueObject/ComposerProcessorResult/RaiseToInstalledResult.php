<?php

declare(strict_types=1);

namespace Rector\Jack\ValueObject\ComposerProcessorResult;

use Rector\Jack\ValueObject\ChangedPackageVersion;
use Webmozart\Assert\Assert;

final class RaiseToInstalledResult
{
    /**
     * @param ChangedPackageVersion[] $changedPackageVersions
     */
    public function __construct(
        private string $composerJsonContents,
        private array $changedPackageVersions,
    ) {
        Assert::allIsInstanceOf($changedPackageVersions, ChangedPackageVersion::class);
    }

    public function getComposerJsonContents(): string
    {
        return $this->composerJsonContents;
    }

    /**
     * @return ChangedPackageVersion[]
     */
    public function getChangedPackageVersions(): array
    {
        return $this->changedPackageVersions;
    }
}
