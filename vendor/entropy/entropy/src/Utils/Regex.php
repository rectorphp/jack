<?php

declare (strict_types=1);
namespace Jack202604\Entropy\Utils;

use Jack202604\Entropy\Attributes\RelatedTest;
use Jack202604\Entropy\Tests\Utils\RegexTest;
/**
 * @api to be used
 */
final class Regex
{
    /**
     * @return array<string, mixed>
     */
    public static function match(string $subject, string $pattern) : array
    {
        $matches = [];
        \preg_match($pattern, $subject, $matches);
        return $matches;
    }
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function matchAll(string $subject, string $pattern) : array
    {
        $matches = [];
        \preg_match_all($pattern, $subject, $matches, \PREG_SET_ORDER);
        return $matches;
    }
    /**
     * @param string|callable $replacement
     */
    public static function replace(string $subject, string $pattern, $replacement) : string
    {
        if (\is_callable($replacement)) {
            return (string) \preg_replace_callback($pattern, $replacement, $subject);
        }
        return \preg_replace($pattern, $replacement, $subject) ?? $subject;
    }
}
