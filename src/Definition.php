<?php

declare(strict_types=1);

namespace Lumivel\Di;

class Definition implements Contracts\Definition
{
    use Concerns\WithArgumentResolver,
        Concerns\WithContainerAware;

    /**
     * The concrete definition
     *
     * @var mixed
     */
    protected $concrete;

    /**
     * Shared state boolean
     *
     * @var bool
     */
    protected bool $shared = false;

    /**
     * Definitions methods
     *
     * @var array<string, mixed>
     */
    protected array $methods;

    public mixed $instance = null;

    /**
     * Definition constructor.
     *
     * @param mixed $concrete The concrete definition.
     */
    public function __construct($concrete)
    {
        $this->concrete = $concrete;
        $this->methods = [];
    }

    public function addArgument(Contracts\Definition\Argument|string $arg): Contracts\Definition
    {
        $this->methods['concreteArgs'][] = $arg;
        return $this;
    }

    public function addArguments(array $args): Contracts\Definition
    {
        foreach ($args as $arg) {
            $this->addArgument($arg);
        }
        return $this;
    }

    public function injectMethodCall(string $method, array|null $params = null): static
    {
        if (is_numeric($method) && is_string($params)) {
            [$method, $args] = [$params, null];
        }

        $this->methods[$method] = $args;
        return $this;
    }

    public function getConcreteArguments(): array
    {
        return !empty($this->methods['concreteArgs'])
        ? $this->methods['concreteArgs']
        : [];
    }

    public function hasMethodCall()
    {
        if (($count = count($this->methods)) > 0) {
            if ($count === 1 && array_key_exists('concreteArgs', $this->methods)) {
                return false;
            }
            return true;
        }
        return false;
    }

    protected function invokeMethods(object $instance)
    {
        foreach ($this->methods as $name => $args) {
            if ($name === 'concreteArgs') {
                continue;
            }

            $callable = [$instance, $name];

            if ($args !== null) {
                call_user_func_array($callable, $args);
                continue;
            }
            call_user_func($callable);
        }
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function make(bool $new = false): mixed
    {
        if ($new || $this->instance === null) {
            $instance = $this->resolve();

            if (is_object($instance)) {
                $this->invokeMethods($instance);
            }
            if ($new || !$this->shared) {
                return $instance;
            }
        }

        if ($this->shared && $this->instance === null) {
            $this->instance = $instance;
        }

        return $this->instance;
    }

    public function resolve(): mixed
    {
        $concrete = $this->concrete;

        if (is_string($concrete) && class_exists($concrete)) {
            return $this->resolveClass($concrete);
        }

        if (is_callable($concrete)) {
            $dependencies = $this->resolveArguments($this->methods);
            return call_user_func_array($concrete, $dependencies);
        }

        return null;
    }

    public function resolveClass($name)
    {
        $dependencies = $this->resolveArguments($this->getConcreteArguments());

        return (new \ReflectionClass($name))->newInstanceArgs($dependencies);
    }

    public function setShared(bool $value = true): static
    {
        $this->shared = $value;
        return $this;
    }
}
