<?php

declare (strict_types=1);
namespace Jack202512\Entropy\Console\Contract;

interface CommandInterface
{
    /**
     * @return non-empty-string
     */
    public function getName() : string;
    /**
     * @return non-empty-string
     */
    public function getDescription() : string;
    // public function run(...)
    // with many arguments
}
