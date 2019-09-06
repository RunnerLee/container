<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-04
 */

namespace Runner\Container;

use Closure;
use Exception;
use ArrayAccess;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Runner\Container\Exceptions\BindingResolutionException;
use Runner\Container\Exceptions\EntryNotFoundException;

class Container implements ArrayAccess, ContainerInterface
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

    /**
     * @var array
     */
    protected $contextual = [];

    /**
     * @param string          $name
     * @param string|callable $concrete
     * @param bool            $share
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
        foreach ((array) $concretes as $concrete) {
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
     * @throws BindingResolutionException
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
     *
     * @return bool
     */
    public function isBound($name)
    {
        return isset($this->instances[$name]) || isset($this->bindings[$name]);
    }

    /**
     * @param $concrete
     * @param ReflectionParameter[] $reflectionParameters
     *
     * @throws Exception
     *
     * @return array
     */
    protected function getDependencies($concrete, array $reflectionParameters)
    {
        $result = [];
        foreach ($reflectionParameters as $parameter) {
            if (!is_null($parameter->getClass())) {
                try {
                    $class = $parameter->getClass()->getName();

                    $result[] = isset($this->contextual[$concrete][$class])
                        ? $this->buildContextualBinding($this->contextual[$concrete][$class])
                        : $this->make($class);
                } catch (Exception $exception) {
                    if (!$parameter->isOptional()) {
                        throw $exception;
                    }
                    $result[] = $parameter->getDefaultValue();
                }
                continue;
            }

            if (isset($this->contextual[$concrete][$name = $parameter->getName()])) {
                $result[] = $this->buildContextualBinding(sprintf('$%s', $this->contextual[$concrete][$name]));
                continue;
            }

            // 如果没有类或接口的类型约束, 并且没有绑定上下文, 且参数没有默认值, 就直接抛异常退出掉
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

        return $result;
    }

    /**
     * @param $implementation
     *
     * @throws ReflectionException
     *
     * @return mixed|object
     */
    protected function buildContextualBinding($implementation)
    {
        if ($implementation instanceof Closure) {
            return $implementation($this);
        }

        if (is_object($implementation)) {
            return $implementation;
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

    /**
     *  {@inheritdoc}
     */
    public function get($id)
    {
        if (!$this->isBound($id)) {
            throw new EntryNotFoundException();
        }

        return $this->make($id);
    }

    /**
     *  {@inheritdoc}
     */
    public function has($id)
    {
        return $this->isBound($id);
    }
}
