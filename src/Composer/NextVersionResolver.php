<?php

declare(strict_types=1);

namespace Rector\Jack\Composer;

use Composer\Semver\VersionParser;
use Nette\Utils\Strings;
use Rector\Jack\Exception\ShouldNotHappenException;

/**
 * @see \Rector\Jack\Tests\Composer\NextVersionResolver\NextVersionResolverTest
 */
final class NextVersionResolver
{
    private const MAJOR = 'major';

    private const MINOR = 'minor';

    public function __construct(
        private readonly VersionParser $versionParser
    ) {
    }

    public function resolve(string $composerVersion): string
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

        if (str_contains($composerVersion, '*')) {
            return $matchVersion[self::MAJOR] . '.' . $matchVersion[self::MINOR] . '.*';
        }

        return '^' . $matchVersion[self::MAJOR] . '.' . $matchVersion[self::MINOR];
    }
}
