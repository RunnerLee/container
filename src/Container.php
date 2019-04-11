<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-04
 */

namespace Runner\Container;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use ReflectionException;
use Exception;

class Container
{
    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * @param string $name
     * @param string|null $concrete
     */
    public function bind($name, $concrete = null)
    {
        if (is_null($concrete)) {
            $concrete = $name;
        }

        $this->bindings[$name] = $concrete;
    }

    /**
     * @param string $name
     * @return object
     * @throws ReflectionException
     */
    public function build($name)
    {
        $concrete = $this->getConcrete($name);

        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException(sprintf('%s is not instantiable', $name));
        }

        $constructor = $reflector->getConstructor();

        if (!$constructor || !$constructor->getParameters()) {
            return $reflector->newInstance();
        }

        return $reflector->newInstanceArgs($this->getDependencies($constructor->getParameters()));
    }

    /**
     * @param ReflectionParameter[] $reflectionParameters
     * @return array
     * @throws
     */
    protected function getDependencies(array $reflectionParameters)
    {
        $result = [];
        foreach ($reflectionParameters as $parameter) {
            if (!is_null($parameter->getClass())) {
                try {
                    $result[] = $this->build($parameter->getClass()->getName());
                } catch (Exception $exception) {
                    if (!$parameter->isOptional()) {
                        throw $exception;
                    }
                    $result[] = $parameter->getDefaultValue();
                }
            } else {
                if (!$parameter->isDefaultValueAvailable()) {
                    throw new RuntimeException(
                        sprintf(
                            'parameter %s has no default value',
                            $parameter->getName()
                        )
                    );
                }
                $result[] = $parameter->getDefaultValue();
            }
        }

        return $result;
    }

    /**
     * @param $name
     * @return string|Closure
     */
    protected function getConcrete($name)
    {
        $concrete = $name;

        while (true) {
            if (!isset($this->bindings[$concrete]) || $concrete === $this->bindings[$concrete]) {
                return $concrete;
            }
            if (($concrete = $this->bindings[$concrete]) instanceof Closure) {
                return $concrete;
            }
        }
    }
}
