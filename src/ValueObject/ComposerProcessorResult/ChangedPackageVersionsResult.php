<?php

declare(strict_types=1);

namespace Rector\Jack\ValueObject\ComposerProcessorResult;

use Entropy\Validation\Assert;
use Rector\Jack\ValueObject\ChangedPackageVersion;

final readonly class ChangedPackageVersionsResult
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
