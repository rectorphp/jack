<?php

declare (strict_types=1);
namespace Rector\Jack\ValueObject\ComposerProcessorResult;

use Rector\Jack\ValueObject\ChangedPackageVersion;
use Jack202508\Webmozart\Assert\Assert;
final class ChangedPackageVersionsResult
{
    /**
     * @readonly
     * @var string
     */
    private $composerJsonContents;
    /**
     * @var ChangedPackageVersion[]
     * @readonly
     */
    private $changedPackageVersions;
    /**
     * @param ChangedPackageVersion[] $changedPackageVersions
     */
    public function __construct(string $composerJsonContents, array $changedPackageVersions)
    {
        $this->composerJsonContents = $composerJsonContents;
        $this->changedPackageVersions = $changedPackageVersions;
        Assert::allIsInstanceOf($changedPackageVersions, ChangedPackageVersion::class);
    }
    public function getComposerJsonContents() : string
    {
        return $this->composerJsonContents;
    }
    /**
     * @return ChangedPackageVersion[]
     */
    public function getChangedPackageVersions() : array
    {
        return $this->changedPackageVersions;
    }
}
