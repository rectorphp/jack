<?php

declare (strict_types=1);
namespace Jack202512\Entropy\FileSystem;

use Jack202512\Entropy\Attributes\RelatedTest;
use Jack202512\Entropy\Tests\FileSystem\FileFinder\FileFinderTest;
final class FileFinder
{
    /**
     * @api used in tests
     * @return string[]
     */
    public static function findPhpFiles(string $directory) : array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            if ($fileInfo->getExtension() !== 'php') {
                continue;
            }
            /** @var \SplFileInfo $fileInfo */
            if (\strpos($fileInfo->getPathname(), 'vendor') !== \false) {
                continue;
            }
            if (\strpos($fileInfo->getPathname(), '/ValueObject/') !== \false) {
                continue;
            }
            $files[] = $fileInfo->getPathname();
        }
        return $files;
    }
}
