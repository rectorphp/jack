<?php

declare(strict_types=1);

namespace Rector\Jack\Tests\ComposerProcessor\OpenVersionsComposerProcessor;

use Nette\Utils\FileSystem;
use Rector\Jack\ComposerProcessor\OpenVersionsComposerProcessor;
use Rector\Jack\Tests\AbstractTestCase;
use Rector\Jack\ValueObject\ChangedPackageVersion;
use Rector\Jack\ValueObject\OutdatedComposer;
use Rector\Jack\ValueObject\OutdatedPackage;

final class OpenVersionsComposerProcessorTest extends AbstractTestCase
{
    private OpenVersionsComposerProcessor $openVersionsComposerProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->openVersionsComposerProcessor = $this->make(OpenVersionsComposerProcessor::class);
    }

    public function test(): void
    {
        $composerJsonContents = FileSystem::read(__DIR__ . '/Fixture/some-closed-composer.json');

        $outdatedComposer = new OutdatedComposer([
            new OutdatedPackage('symfony/console', '5.4.0', '^5.4', true, '6.4.0', '1 year'),
        ]);

        $changedPackageVersionsResult = $this->openVersionsComposerProcessor->process(
            $composerJsonContents,
            $outdatedComposer,
            10,
            false,
            null
        );

        $this->assertCount(1, $changedPackageVersionsResult->getChangedPackageVersions());
        $this->assertContainsOnlyInstancesOf(
            ChangedPackageVersion::class,
            $changedPackageVersionsResult->getChangedPackageVersions()
        );

        $this->assertStringEqualsFile(
            __DIR__ . '/Fixture/expected-opened-composer.json',
            $changedPackageVersionsResult->getComposerJsonContents()
        );
    }
}
