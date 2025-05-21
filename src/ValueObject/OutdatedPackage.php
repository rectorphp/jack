<?php

declare(strict_types=1);

namespace Rector\Jack\ValueObject;

use Nette\Utils\Strings;

final readonly class OutdatedPackage
{
    public function __construct(
        private string $name,
        private string $currentVersion,
        private string $composerVersion,
        private bool $isProd,
        private string $latestVersion,
        // nullable on composer 2.7-
        private ?string $currentVersionAge,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    public function getComposerVersion(): string
    {
        return $this->composerVersion;
    }

    public function isProd(): bool
    {
        return $this->isProd;
    }

    public function getLatestVersion(): string
    {
        return $this->latestVersion;
    }

    public function getCurrentVersionAge(): ?string
    {
        return $this->currentVersionAge;
    }

    public function isVeryOld(): bool
    {
        if ($this->currentVersionAge === null) {
            return true;
        }

        $matchYears = Strings::match($this->currentVersionAge, '#[3-9] years#');
        return $matchYears !== null;
    }
}
