<?php

declare(strict_types=1);

namespace Rector\Jack\ValueObject;

final readonly class ChangedPackageVersion
{
    public function __construct(
        private string $packageName,
        private string $oldVersion,
        private string $newVersion,
    ) {

    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getOldVersion(): string
    {
        return $this->oldVersion;
    }

    public function getNewVersion(): string
    {
        return $this->newVersion;
    }
}
