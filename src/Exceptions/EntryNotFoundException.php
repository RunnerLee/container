<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-09
 */

namespace Runner\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class EntryNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
