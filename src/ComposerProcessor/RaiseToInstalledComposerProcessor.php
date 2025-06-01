<?php

declare (strict_types=1);
namespace Rector\Jack\ComposerProcessor;

use Jack202506\Composer\Semver\Comparator;
use Jack202506\Composer\Semver\VersionParser;
use Jack202506\Nette\Utils\Json;
use Rector\Jack\Composer\InstalledVersionResolver;
use Rector\Jack\Composer\VersionComparator;
use Rector\Jack\FileSystem\ComposerJsonPackageVersionUpdater;
use Rector\Jack\ValueObject\ChangedPackageVersion;
use Rector\Jack\ValueObject\ComposerProcessorResult\ChangedPackageVersionsResult;
/**
 * @see \Rector\Jack\Tests\ComposerProcessor\RaiseToInstalledComposerProcessor\RaiseToInstalledComposerProcessorTest
 */
final class RaiseToInstalledComposerProcessor
{
    /**
     * @readonly
     * @var \Composer\Semver\VersionParser
     */
    private $versionParser;
    /**
     * @readonly
     * @var \Rector\Jack\Composer\InstalledVersionResolver
     */
    private $installedVersionResolver;
    public function __construct(VersionParser $versionParser, InstalledVersionResolver $installedVersionResolver)
    {
        $this->versionParser = $versionParser;
        $this->installedVersionResolver = $installedVersionResolver;
    }
    public function process(string $composerJsonContents) : ChangedPackageVersionsResult
    {
        $installedPackagesToVersions = $this->installedVersionResolver->resolve();
        $composerJson = Json::decode($composerJsonContents, \true);
        $changedPackageVersions = [];
        // iterate require and require-dev sections and check if installed version is newer one than in composer.json
        // if so, replace it
        foreach ($composerJson['require'] ?? [] as $packageName => $packageVersion) {
            if (!isset($installedPackagesToVersions[$packageName])) {
                continue;
            }
            if ($this->shouldSkipPackageVersion($packageVersion)) {
                continue;
            }
            $installedVersion = $installedPackagesToVersions[$packageName];
            // special case for unions
            if (\strpos((string) $packageVersion, '|') !== \false) {
                $passingVersionKeys = [];
                $unionPackageVersions = \explode('|', (string) $packageVersion);
                foreach ($unionPackageVersions as $key => $unionPackageVersion) {
                    $unionPackageConstraint = $this->versionParser->parseConstraints($unionPackageVersion);
                    if (Comparator::greaterThanOrEqualTo($installedVersion, $unionPackageConstraint->getLowerBound()->getVersion())) {
                        $passingVersionKeys[] = $key;
                    }
                }
                // nothing we can do, as lower union version is passing
                if ($passingVersionKeys === [0]) {
                    continue;
                }
                // higher version is meet, let's drop the lower one
                if ($passingVersionKeys === [0, 1]) {
                    $newPackageVersion = $unionPackageVersions[1];
                    $composerJsonContents = ComposerJsonPackageVersionUpdater::update($composerJsonContents, $packageName, $newPackageVersion);
                    $changedPackageVersions[] = new ChangedPackageVersion($packageName, $packageVersion, $newPackageVersion);
                    continue;
                }
            }
            $normalizedInstalledVersion = $this->versionParser->normalize($installedVersion);
            $installedPackageConstraint = $this->versionParser->parseConstraints($packageVersion);
            $normalizedConstraintVersion = $this->versionParser->normalize($installedPackageConstraint->getLowerBound()->getVersion());
            // remove "-dev" suffix
            $normalizedConstraintVersion = \str_replace('-dev', '', $normalizedConstraintVersion);
            // are major + minor equal?
            if (VersionComparator::areAndMinorVersionsEqual($normalizedConstraintVersion, $normalizedInstalledVersion)) {
                continue;
            }
            [$major, $minor, $patch] = \explode('.', $normalizedInstalledVersion);
            $newRequiredVersion = \sprintf('^%s.%s', $major, $minor);
            // lets update
            $composerJsonContents = ComposerJsonPackageVersionUpdater::update($composerJsonContents, $packageName, $newRequiredVersion);
            // focus on minor only
            // or on patch in case of 0.*
            $changedPackageVersions[] = new ChangedPackageVersion($packageName, $packageVersion, $newRequiredVersion);
        }
        return new ChangedPackageVersionsResult($composerJsonContents, $changedPackageVersions);
    }
    private function shouldSkipPackageVersion(string $packageVersion) : bool
    {
        return \strpos($packageVersion, 'dev-') !== \false;
    }
}
