<?php

declare(strict_types=1);

namespace Rector\Jack\Tests\Composer\NextVersionResolver;

use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Jack\Composer\NextVersionResolver;
use Rector\Jack\Tests\AbstractTestCase;

final class NextVersionResolverTest extends AbstractTestCase
{
    private NextVersionResolver $nextVersionResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nextVersionResolver = $this->make(NextVersionResolver::class);
    }

    #[DataProvider('provideData')]
    public function test(string $composerVersion, string $expectedVersion): void
    {
        $nextVersion = $this->nextVersionResolver->resolve($composerVersion);
        $this->assertSame($expectedVersion, $nextVersion);
    }

    /**
     * @return iterable<string[]>
     */
    public static function provideData(): iterable
    {
        yield ['^1.0', '^2.0'];
        yield ['2.2.*', '2.3.*'];
        yield ['4.*', '5.0.*'];
    }
}
