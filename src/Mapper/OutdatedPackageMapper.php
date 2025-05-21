<?php

declare (strict_types=1);
namespace Rector\Jack\Mapper;

use Jack202505\Nette\Utils\FileSystem;
use Jack202505\Nette\Utils\Json;
use Rector\Jack\ValueObject\OutdatedPackage;
final class OutdatedPackageMapper
{
    /**
     * @param array<array<string, mixed>> $outdatedPackages
     *
     * @return OutdatedPackage[]
     */
    public function mapToObjects(array $outdatedPackages, string $composerJsonFilePath) : array
    {
        $prodPackagesToVersions = $this->resolveRequiredPackages($composerJsonFilePath, 'require');
        $devPackagesToVersions = $this->resolveRequiredPackages($composerJsonFilePath, 'require-dev');
        return \array_map(function (array $data) use($prodPackagesToVersions, $devPackagesToVersions) : OutdatedPackage {
            $packageName = $data['name'];
            $isProd = \array_key_exists($packageName, $prodPackagesToVersions);
            $composerVersions = $prodPackagesToVersions[$packageName] ?? $devPackagesToVersions[$packageName];
            return new OutdatedPackage($packageName, $data['version'], $composerVersions, $isProd, $data['latest'], $data['release-age'] ?? null);
        }, $outdatedPackages);
    }
    /**
     * @return array<string, string>
     */
    private function resolveRequiredPackages(string $composerJsonFilePath, string $section) : array
    {
        $composerJson = $this->parseComposerJsonToJson($composerJsonFilePath);
        return (array) ($composerJson[$section] ?? []);
    }
    /**
     * @return array<string, mixed>
     */
    private function parseComposerJsonToJson(string $composerJsonFilePath) : array
    {
        $composerJsonContents = FileSystem::read($composerJsonFilePath);
        return (array) Json::decode($composerJsonContents, \true);
    }
}
