<?php

namespace Jack202506\Illuminate\Contracts\Container;

use Exception;
use Jack202506\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
