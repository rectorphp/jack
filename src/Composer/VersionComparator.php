<?php

namespace Rector\Jack\Composer;

final class VersionComparator
{
    public static function areAndMinorVersionsEqual(string $firstVersion, string $secondVersion) : bool
    {
        [$firstMajor, $firstMinor] = \explode('.', $firstVersion);
        [$secondMajor, $secondMinor] = \explode('.', $secondVersion);
        // if major and minor are equal, we can skip the update
        return $firstMajor === $secondMajor && $firstMinor === $secondMinor;
    }
}
