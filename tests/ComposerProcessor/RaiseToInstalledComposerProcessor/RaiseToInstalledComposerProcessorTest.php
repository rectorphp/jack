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

        $raiseToInstalledResult = $this->raiseToInstalledComposerProcessor->process($composerJsonContents);

        $this->assertCount(1, $raiseToInstalledResult->getChangedPackageVersions());
        $this->assertContainsOnlyInstancesOf(
            ChangedPackageVersion::class,
            $raiseToInstalledResult->getChangedPackageVersions()
        );

        $changedPackageVersion = $raiseToInstalledResult->getChangedPackageVersions()[0];

        $this->assertSame('illuminate/container', $changedPackageVersion->getPackageName());
        $this->assertSame('^9.0', $changedPackageVersion->getOldVersion());

        // note: this might change in near future; improve to dynamic soon
        $this->assertSame('^12.14', $changedPackageVersion->getNewVersion());
    }
}
