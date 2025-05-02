<?php

declare (strict_types=1);
namespace Rector\Jack\Composer;

use Jack202505\Nette\Utils\FileSystem;
use Jack202505\Symfony\Component\Process\Process;
final class ComposerOutdatedResponseProvider
{
    public function provide() : string
    {
        // load from cache, temporarily - @todo cache on json hash + week timeout
        $outdatedFilename = __DIR__ . '/../../dumped-outdated.json';
        if (\is_file($outdatedFilename)) {
            return FileSystem::read($outdatedFilename);
        }
        $composerOutdatedProcess = Process::fromShellCommandline('composer outdated --direct --major-only --format json', null, null, null, 120);
        $composerOutdatedProcess->mustRun();
        $processResult = $composerOutdatedProcess->getOutput();
        FileSystem::write($outdatedFilename, $processResult);
        return $processResult;
    }
}
