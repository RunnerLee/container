<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-04
 */

namespace Runner\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class BindingResolutionException extends RuntimeException implements ContainerExceptionInterface
{
}
