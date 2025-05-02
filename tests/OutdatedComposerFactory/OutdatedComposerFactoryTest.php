<?php

declare(strict_types=1);

namespace Rector\Jack\Tests\OutdatedComposerFactory;

use Rector\Jack\OutdatedComposerFactory;
use Rector\Jack\Tests\AbstractTestCase;
use Rector\Jack\ValueObject\OutdatedPackage;

final class OutdatedComposerFactoryTest extends AbstractTestCase
{
    public function test(): void
    {
        $outdatedComposerFactory = $this->make(OutdatedComposerFactory::class);

        $outdatedComposer = $outdatedComposerFactory->createOutdatedComposer([
            [
                'name' => 'symfony/console',
                'direct-dependency' => true,
                'homepage' => 'https://symfony.com',
                'source' => 'https://github.com/symfony/console/tree/v6.4.20',
                'version' => 'v6.4.20',
                'release-age' => '1 month old',
                'release-date' => '2025-03-03T17:16:38+00:00',
                'latest' => 'v7.2.6',
                'latest-status' => 'update-possible',
                'latest-release-date' => '2025-04-07T19:09:28+00:00',
                'description' => 'Eases the creation of beautiful and testable command line interfaces',
                'abandoned' => false,
            ],
        ], __DIR__ . '/Fixture/some-composer.json');

        $this->assertCount(1, $outdatedComposer->getProdPackages());
        $this->assertContainsOnlyInstancesOf(OutdatedPackage::class, $outdatedComposer->getProdPackages());

        $this->assertCount(0, $outdatedComposer->getDevPackages());
    }
}
