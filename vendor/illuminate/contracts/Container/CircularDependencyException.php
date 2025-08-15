<?php

namespace Jack202508\Illuminate\Contracts\Container;

use Exception;
use Jack202508\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
