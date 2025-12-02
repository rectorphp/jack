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

        $skippedKeys = ['suggest', 'replace', 'provide', 'conflict'];

        foreach ($skippedKeys as $skippedKey) {
            $regexKeyContent = sprintf('#"%s"\s*:\s*{[^}]*}#', $skippedKey);
            $skippedContent = Strings::match($composerJsonContents, $regexKeyContent);

            if ($skippedContent !== null) {
                $allChanges = Strings::replace($allChanges, $regexKeyContent, $skippedContent[0]);
            }
        }

        return $allChanges;
    }
}
