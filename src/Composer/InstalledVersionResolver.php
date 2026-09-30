<?php

declare(strict_types=1);

namespace Rector\Jack\Composer;

use Rector\Jack\Exception\ShouldNotHappenException;
use Rector\Jack\Utils\JsonFileLoader;

final class InstalledVersionResolver
{
    /**
     * @return array<string, string>
     */
    public function resolve(): array
    {
        $installedJsonFilePath = getcwd() . '/vendor/composer/installed.json';

        $installedJson = JsonFileLoader::loadFileToJson($installedJsonFilePath);
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
