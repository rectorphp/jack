<?php

namespace Jack202511\Illuminate\Contracts\Container;

use Exception;
use Jack202511\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
