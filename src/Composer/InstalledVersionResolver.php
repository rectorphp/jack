<?php

declare(strict_types=1);

namespace Rector\Jack\Composer;

use Rector\Jack\Exception\ShouldNotHappenException;
use Rector\Jack\Utils\JsonFileLoader;

final class InstalledVersionResolver
{
    private string $installedJsonFilePath;

    public function __construct(string $installedJsonFilePath)
    {
        $this->installedJsonFilePath = $installedJsonFilePath;
    }

    /**
     * @return array<string, string>
     */
    public function resolve(): array
    {
        $installedJson = JsonFileLoader::loadFileToJson($this->installedJsonFilePath);
        if (! array_key_exists('packages', $installedJson)) {
            throw new ShouldNotHappenException('Missing "packages" key in "installed.json"');
        }

        $installedPackagesToVersions = [];
        foreach ($installedJson['packages'] as $installedPackage) {
            $packageName = $installedPackage['name'];
            $packageVersion = $installedPackage['version'];

            $installedPackagesToVersions[$packageName] = $packageVersion;
        }

        return $installedPackagesToVersions;
    }
}
