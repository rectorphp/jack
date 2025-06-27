<?php

declare(strict_types=1);

namespace Rector\Jack\FileSystem;

use Nette\Utils\Strings;

final class ComposerJsonPackageVersionUpdater
{
    public static function update(string $composerJsonContents, string $packageName, string $newVersion): string
    {
        // replace using regex, to keep original composer.json format
        $allChanges = Strings::replace(
            $composerJsonContents,
            // find
            sprintf('#"%s": "(.*?)"#', $packageName),
            // replace
            sprintf('"%s": "%s"', $packageName, $newVersion)
        );

        $suggestContent = Strings::match($composerJsonContents, '#"suggest"\s*:\s*{[^}]*}#');

        if ($suggestContent !== null) {
            $allChanges = Strings::replace(
                $allChanges,
                '#"suggest"\s*:\s*{[^}]*}#',
                $suggestContent[0]
            );
        }

        return $allChanges;
    }
}
