<?php

declare(strict_types=1);

namespace Rector\Jack\Tests\ComposerProcessor\RaiseToInstalledComposerProcessor;

use Nette\Utils\FileSystem;
use Rector\Jack\ComposerProcessor\RaiseToInstalledComposerProcessor;
use Rector\Jack\Tests\AbstractTestCase;
use Rector\Jack\ValueObject\ChangedPackageVersion;

final class RaiseToInstalledComposerProcessorTest extends AbstractTestCase
{
    private RaiseToInstalledComposerProcessor $raiseToInstalledComposerProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->raiseToInstalledComposerProcessor = $this->make(RaiseToInstalledComposerProcessor::class);
    }

    public function test(): void
    {
        $composerJsonContents = FileSystem::read(__DIR__ . '/Fixture/some-outdated-composer.json');

        $changedPackageVersionsResult = $this->raiseToInstalledComposerProcessor->process($composerJsonContents);

        $this->assertCount(1, $changedPackageVersionsResult->getChangedPackageVersions());
        $this->assertContainsOnlyInstancesOf(
            ChangedPackageVersion::class,
            $changedPackageVersionsResult->getChangedPackageVersions()
        );

        $changedPackageVersion = $changedPackageVersionsResult->getChangedPackageVersions()[0];

        $this->assertSame('illuminate/container', $changedPackageVersion->getPackageName());
        $this->assertSame('^9.0', $changedPackageVersion->getOldVersion());

        // note: this might change in near future; improve to dynamic soon
        $this->assertStringStartsWith('^12.1', $changedPackageVersion->getNewVersion());
    }

    public function testSkipDev(): void
    {
        $composerJsonContents = FileSystem::read(__DIR__ . '/Fixture/skip-dev.json');
        $changedPackageVersionsResult = $this->raiseToInstalledComposerProcessor->process($composerJsonContents);

        $this->assertEmpty($changedPackageVersionsResult->getChangedPackageVersions());
    }

    public function testSinglePiped(): void
    {
        $composerJsonContents = FileSystem::read(__DIR__ . '/Fixture/single-piped.json');

        $changedPackageVersionsResult = $this->raiseToInstalledComposerProcessor->process($composerJsonContents);

        $changedPackageVersion = $changedPackageVersionsResult->getChangedPackageVersions()[0];

        $this->assertSame('illuminate/container', $changedPackageVersion->getPackageName());
        $this->assertSame('^12.14 | 13.0', $changedPackageVersion->getOldVersion());
        $this->assertSame('^12.18', $changedPackageVersion->getNewVersion());
    }

    public function testDoublePiped(): void
    {
        $composerJsonContents = FileSystem::read(__DIR__ . '/Fixture/double-piped.json');

        $changedPackageVersionsResult = $this->raiseToInstalledComposerProcessor->process($composerJsonContents);

        $changedPackageVersion = $changedPackageVersionsResult->getChangedPackageVersions()[0];

        $this->assertSame('illuminate/container', $changedPackageVersion->getPackageName());
        $this->assertSame('^12.14 | 13.0', $changedPackageVersion->getOldVersion());
        $this->assertSame('^12.18', $changedPackageVersion->getNewVersion());
    }
}
