<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-04
 */

namespace Runner\Container;

use ArrayAccess;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Runner\Container\Exceptions\BindingResolutionException;

class Container implements ArrayAccess
{
    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var array
     */
    protected $shares = [];

    protected $contextual = [];

    /**
     * @param $name
     * @param null $concrete
     * @param bool $share
     */
    public function bind($name, $concrete = null, $share = false)
    {
        if (is_null($concrete)) {
            $concrete = $name;
        }

        $this->bindings[$name] = $concrete;

        $share && $this->shares[$name] = true;
    }

    /**
     * @param $concretes
     * @param $parameter
     * @param $implementation
     */
    public function bindContext($concretes, $parameter, $implementation)
    {
        foreach ((array)$concretes as $concrete) {
            $this->contextual[$concrete][$parameter] = $implementation;
        }
    }

    /**
     * @param $name
     * @param $instance
     */
    public function instance($name, $instance)
    {
        $this->instances[$name] = $instance;
    }

    /**
     * @param string $name
     *
     * @throws ReflectionException
     *
     * @return object
     */
    public function make($name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $instance = $this->build($name);

        if (isset($this->shares[$name])) {
            $this->instances[$name] = $instance;
        }

        return $instance;
    }

    /**
     * @param $name
     *
     * @throws ReflectionException
     *
     * @return mixed|object
     */
    protected function build($name)
    {
        $concrete = $this->getConcrete($name);

        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new BindingResolutionException(sprintf('%s is not instantiable', $name));
        }

        $constructor = $reflector->getConstructor();

        if (!$constructor || !$constructor->getParameters()) {
            return $reflector->newInstance();
        }

        return $reflector->newInstanceArgs($this->getDependencies($concrete, $constructor->getParameters()));
    }

    /**
     * @param $name
     * @return bool
     */
    public function isBound($name)
    {
        return isset($this->instances[$name]) || isset($this->bindings[$name]);
    }

    /**
     * @param $concrete
     * @param ReflectionParameter[] $reflectionParameters
     * @return array
     * @throws Exception
     */
    protected function getDependencies($concrete, array $reflectionParameters)
    {
        $result = [];
        foreach ($reflectionParameters as $parameter) {
            if (!is_null($parameter->getClass())) {
                try {
                    $class = $parameter->getClass()->getName();
                    if (isset($this->contextual[$concrete][$class])) {
                        $result[] = $this->buildContextualBinding($this->contextual[$concrete][$class]);
                    } else {
                        $result[] = $this->make($class);
                    }
                } catch (Exception $exception) {
                    if (!$parameter->isOptional()) {
                        throw $exception;
                    }
                    $result[] = $parameter->getDefaultValue();
                }
            } else {
                if (!$parameter->isDefaultValueAvailable()) {
                    throw new BindingResolutionException(
                        sprintf(
                            'parameter %s has no default value in %s',
                            $parameter->getName(),
                            $parameter->getDeclaringClass()->getName()
                        )
                    );
                }
                $result[] = $parameter->getDefaultValue();
            }
        }

        return $result;
    }

    /**
     * @param $implementation
     * @return mixed|object
     * @throws ReflectionException
     */
    protected function buildContextualBinding($implementation)
    {
        if ($implementation instanceof Closure) {
            return $implementation($this);
        }
        return $this->make($implementation);
    }

    /**
     * @param $name
     *
     * @return string|Closure
     */
    protected function getConcrete($name)
    {
        $concrete = $this->bindings[$name] ?? $name;

        if (!is_object($concrete) && $concrete !== $name && isset($this->bindings[$concrete])) {
            $concrete = function () use ($concrete) {
                return $this->make($concrete);
            };
        }

        return $concrete;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->isBound($offset);
    }

    /**
     * @param mixed $offset
     *
     * @throws ReflectionException
     *
     * @return mixed|object
     */
    public function offsetGet($offset)
    {
        return $this->make($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->instances[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->instances[$offset]);
    }
}
