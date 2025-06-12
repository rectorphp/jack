<?php

declare (strict_types=1);
namespace Rector\Jack\Utils;

use Jack202506\Nette\Utils\FileSystem;
use Jack202506\Nette\Utils\Json;
use Jack202506\Webmozart\Assert\Assert;
final class JsonFileLoader
{
    /**
     * @return array<string, mixed>
     */
    public static function loadFileToJson(string $filePath) : array
    {
        Assert::fileExists($filePath);
        $fileContents = FileSystem::read($filePath);
        return Json::decode($fileContents, \true);
    }
}
