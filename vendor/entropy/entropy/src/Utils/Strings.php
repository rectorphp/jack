<?php

declare (strict_types=1);
namespace Jack202604\Entropy\Utils;

use Jack202604\Entropy\Attributes\RelatedTest;
use Jack202604\Entropy\Tests\Utils\StringsTest;
/**
 * @api to be used outside
 */
final class Strings
{
    public static function webalize(string $text) : string
    {
        $text = (string) \preg_replace('/[^\\p{L}\\p{N}]+/u', '-', $text);
        $text = \trim($text, '-');
        return \strtolower($text);
    }
}
