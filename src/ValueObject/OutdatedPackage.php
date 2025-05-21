<?php

declare (strict_types=1);
namespace Rector\Jack\ValueObject;

use Jack202505\Nette\Utils\Strings;
final class OutdatedPackage
{
    /**
     * @readonly
     * @var string
     */
    private $name;
    /**
     * @readonly
     * @var string
     */
    private $currentVersion;
    /**
     * @readonly
     * @var string
     */
    private $composerVersion;
    /**
     * @readonly
     * @var bool
     */
    private $isProd;
    /**
     * @readonly
     * @var string
     */
    private $latestVersion;
    /**
     * @readonly
     * @var string|null
     */
    private $currentVersionAge;
    public function __construct(string $name, string $currentVersion, string $composerVersion, bool $isProd, string $latestVersion, ?string $currentVersionAge)
    {
        $this->name = $name;
        $this->currentVersion = $currentVersion;
        $this->composerVersion = $composerVersion;
        $this->isProd = $isProd;
        $this->latestVersion = $latestVersion;
        // nullable on composer 2.7-
        $this->currentVersionAge = $currentVersionAge;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getCurrentVersion() : string
    {
        return $this->currentVersion;
    }
    public function getComposerVersion() : string
    {
        return $this->composerVersion;
    }
    public function isProd() : bool
    {
        return $this->isProd;
    }
    public function getLatestVersion() : string
    {
        return $this->latestVersion;
    }
    public function getCurrentVersionAge() : ?string
    {
        return $this->currentVersionAge;
    }
    public function isVeryOld() : bool
    {
        if ($this->currentVersionAge === null) {
            return \true;
        }
        $matchYears = Strings::match($this->currentVersionAge, '#[3-9] years#');
        return $matchYears !== null;
    }
}
