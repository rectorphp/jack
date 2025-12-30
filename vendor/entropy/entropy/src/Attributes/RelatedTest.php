<?php

declare (strict_types=1);
namespace Jack202512\Entropy\Attributes;

use Attribute;
use Jack202512\PHPUnit\Framework\TestCase;
#[Attribute(Attribute::TARGET_CLASS)]
final class RelatedTest
{
    /**
     * @param class-string<TestCase> $testClass
     */
    public function __construct(string $testClass)
    {
    }
}
