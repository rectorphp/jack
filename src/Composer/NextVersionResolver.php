<?php

declare (strict_types=1);
namespace Rector\Jack\Composer;

use Jack202508\Composer\Semver\VersionParser;
use Jack202508\Nette\Utils\Strings;
use Rector\Jack\Exception\ShouldNotHappenException;
/**
 * @see \Rector\Jack\Tests\Composer\NextVersionResolver\NextVersionResolverTest
 */
final class NextVersionResolver
{
    /**
     * @readonly
     * @var \Composer\Semver\VersionParser
     */
    private $versionParser;
    private const MAJOR = 'major';
    private const MINOR = 'minor';
    public function __construct(VersionParser $versionParser)
    {
        $this->versionParser = $versionParser;
    }
    public function resolve(string $packageName, string $composerVersion) : string
    {
        $constraint = $this->versionParser->parseConstraints($composerVersion);
        $nextBound = $constraint->getUpperBound();
        $matchVersion = Strings::match($nextBound->getVersion(), '#^(?<' . self::MAJOR . '>\\d+)\\.(?<' . self::MINOR . '>\\d+)#');
        if ($matchVersion === null) {
            throw new ShouldNotHappenException(\sprintf('Unable to parse major and minor value from composer version "%s"', $composerVersion));
        }
        // special case for "symfony/*" packages as version jump is huge there
        if (\strpos($composerVersion, '*') !== \false || \strncmp($packageName, 'symfony/', \strlen('symfony/')) === 0) {
            return $matchVersion[self::MAJOR] . '.' . $matchVersion[self::MINOR] . '.*';
        }
        return '^' . $matchVersion[self::MAJOR] . '.' . $matchVersion[self::MINOR];
    }
}
