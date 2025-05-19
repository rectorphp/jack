<?php

declare(strict_types=1);

namespace Rector\Jack\Composer;

use Rector\Jack\Utils\JsonFileLoader;
use Webmozart\Assert\Assert;

final class InstalledVersionResolver
{
    /**
     * @return array<string, string>
     */
    public function resolve(): array
    {
        $installedJsonFilePath = getcwd() . '/vendor/composer/installed.json';

        $installedJson = JsonFileLoader::loadFileToJson($installedJsonFilePath);
        Assert::keyExists($installedJson, 'packages');

        $installedPackagesToVersions = [];
        foreach ($installedJson['packages'] as $installedPackage) {
            $packageName = $installedPackage['name'];
            $packageVersion = $installedPackage['version'];

            $installedPackagesToVersions[$packageName] = $packageVersion;
        }

        return $installedPackagesToVersions;
    }
}
