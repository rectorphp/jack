<?php

declare(strict_types=1);

namespace Rector\Jack\Composer;

use Nette\Utils\DateTime;
use Nette\Utils\FileSystem;
use Symfony\Component\Process\Process;

final class ComposerOutdatedResponseProvider
{
    public function provide(): string
    {
        $composerOutdatedFilePath = $this->resolveComposerOutdatedFilePath();

        // let's use cache
        if ($this->shouldLoadCacheFile($composerOutdatedFilePath)) {
            /** @var string $composerOutdatedFilePath */
            return FileSystem::read($composerOutdatedFilePath);
        }

        $composerOutdatedProcess = Process::fromShellCommandline(
            'composer outdated --direct --major-only --format json',
            timeout: 120
        );

        $composerOutdatedProcess->mustRun();

        $processResult = $composerOutdatedProcess->getOutput();

        if (is_string($composerOutdatedFilePath)) {
            FileSystem::write($composerOutdatedFilePath, $processResult);
        }

        return $processResult;
    }

    private function resolveProjectComposerHash(): ?string
    {
        if (file_exists(getcwd() . '/composer.lock')) {
            return sha1(getcwd() . '/composer.lock');
        }

        if (file_exists(getcwd() . '/composer.json')) {
            return getcwd() . '/composer.json';
        }

        return null;
    }

    private function resolveComposerOutdatedFilePath(): ?string
    {
        $projectComposerHash = $this->resolveProjectComposerHash();
        if ($projectComposerHash) {
            // load from cache, temporarily - @todo cache on json hash + week timeout
            return sys_get_temp_dir() . '/jack/composer-outdated-' . $projectComposerHash . '.json';
        }

        return null;
    }

    private function isFileYoungerThanWeek(string $filePath): bool
    {
        $fileTime = filemtime($filePath);
        if ($fileTime === false) {
            return false;
        }

        return (time() - $fileTime) < DateTime::WEEK;
    }

    private function shouldLoadCacheFile(?string $cacheFilePath): bool
    {
        if (! is_string($cacheFilePath)) {
            return false;
        }

        if (! file_exists($cacheFilePath)) {
            return false;
        }

        return $this->isFileYoungerThanWeek($cacheFilePath);
    }
}
