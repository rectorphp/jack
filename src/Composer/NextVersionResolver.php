<?php

declare(strict_types=1);

namespace Rector\Jack\Composer;

use Composer\Semver\VersionParser;
use Nette\Utils\Strings;
use Rector\Jack\Exception\ShouldNotHappenException;

/**
 * @see \Rector\Jack\Tests\Composer\NextVersionResolver\NextVersionResolverTest
 */
final readonly class NextVersionResolver
{
    private const MAJOR = 'major';

    private const MINOR = 'minor';

    public function __construct(
        private VersionParser $versionParser
    ) {
    }

    public function resolve(string $packageName, string $composerVersion): string
    {
        $constraint = $this->versionParser->parseConstraints($composerVersion);

        $nextBound = $constraint->getUpperBound();
        $matchVersion = Strings::match(
            $nextBound->getVersion(),
            '#^(?<' . self::MAJOR . '>\d+)\.(?<' . self::MINOR . '>\d+)#'
        );

        if ($matchVersion === null) {
            throw new ShouldNotHappenException(
                sprintf('Unable to parse major and minor value from composer version "%s"', $composerVersion)
            );
        }

        // special case for "symfony/*" packages as version jump is huge there
        if (str_contains($composerVersion, '*') || str_starts_with($packageName, 'symfony/')) {
            return $matchVersion[self::MAJOR] . '.' . $matchVersion[self::MINOR] . '.*';
        }

        return '^' . $matchVersion[self::MAJOR] . '.' . $matchVersion[self::MINOR];
    }
}
