<?php

declare(strict_types=1);

namespace Rector\Jack\Mapper;

use Rector\Jack\ValueObject\OutdatedPackage;
use Webmozart\Assert\Assert;

final class OutdatedPackageMapper
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $cachedComposerJson = [];

    /**
     * @param array<array<string, mixed>> $outdatedPackages
     *
     * @return OutdatedPackage[]
     */
    public function mapToObjects(array $outdatedPackages, string $composerJsonFilePath): array
    {
        $prodPackagesToVersions = $this->resolveRequiredPackages($composerJsonFilePath, 'require');
        $devPackagesToVersions = $this->resolveRequiredPackages($composerJsonFilePath, 'require-dev');

        return array_map(function (array $data) use ($prodPackagesToVersions, $devPackagesToVersions): OutdatedPackage {
            $packageName = $data['name'];

            $isProd = array_key_exists($packageName, $prodPackagesToVersions);
            $composerVersions = $prodPackagesToVersions[$packageName] ?? $devPackagesToVersions[$packageName];

            return new OutdatedPackage(
                $packageName,
                $data['version'],
                $composerVersions,
                $isProd,
                $data['latest'],
                $data['release-age'] ?? null
            );
        }, $outdatedPackages);
    }

    /**
     * @return array<string, string>
     */
    private function resolveRequiredPackages(string $composerJsonFilePath, string $section): array
    {
        $composerJson = $this->parseComposerJsonToJson($composerJsonFilePath);

        return (array) ($composerJson[$section] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseComposerJsonToJson(string $composerJsonFilePath): array
    {
        if (isset($this->cachedComposerJson[$composerJsonFilePath])) {
            return $this->cachedComposerJson[$composerJsonFilePath];
        }

        // use native functions to ease re-use by 3rd party packages
        $composerJsonContents = file_get_contents($composerJsonFilePath);
        Assert::string($composerJsonContents);

        $composerJson = (array) json_decode($composerJsonContents, true);


        $this->cachedComposerJson[$composerJsonFilePath] = $composerJson;

        return $composerJson;
    }
}
