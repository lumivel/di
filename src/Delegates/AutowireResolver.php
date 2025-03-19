<?php

declare(strict_types=1);

namespace Lumivel\Di\Delegates;

use Lumivel\Di\Concerns\WithArgumentResolver;
use Lumivel\Di\Concerns\WithContainerAware;
use Lumivel\Di\Contracts\Delegate;
use Lumivel\Di\Throws\ContainerException;
use Lumivel\Di\Throws\InvalidArgumentException;
use Lumivel\Di\Throws\NotFoundException;
use ReflectionClass;
use ReflectionUnionType;

use function sprintf;

/**
 * AutowireResolver class delegate.
 *
 * This delegate enables container registry to auto resolve services. It is
 * responsible for the delegation of classes that are not registered to the
 * container registry.
 */
class AutowireResolver implements Delegate
{
    use WithArgumentResolver,
        WithContainerAware;

    public function get(string $identifier, $args = []): mixed
    {
        if (!$this->has($identifier)) {
            throw new NotFoundException(
                "Service ($identifier) is not an existing class and therefore cannot be resolved.",
            );
        }

        try {
            $reflectionClass = new ReflectionClass($identifier);
            $constructor = $reflectionClass->getConstructor();

            if ($constructor === null) {
                return new $identifier;
            }

            $dependencies = $constructor->getParameters();
            $this->resolveReflectionArguments($dependencies, $args);

            return $reflectionClass->newInstanceArgs($dependencies);
        } catch (\ReflectionException $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    public function has(string $identifier): bool
    {
        return class_exists($identifier);
    }

    /**
     * Auto resolve service dependencies recursively.
     *
     * This method is responsible for resolving the dependencies of a service
     *
     * @param \ReflectionParameter[] $dependencies The reflection parameters of
     * the dependencies
     * @param array $params parameters to that override the default dependencies
     *
     * @return void
     * @throws \Lumivel\Di\Throws\InvalidArgumentException If the dependency
     * cannot be resolved
     */
    protected function resolveReflectionArguments(array &$dependencies, array $params)
    {
        foreach ($dependencies as &$dependency) {
            $name = $dependency->getName();
            $type = $dependency->getType();

            if (array_key_exists($name, $params) && !($type instanceof ReflectionUnionType)) {
                $dependency = $params[$name];
                continue;
            } elseif ($type && !$type->isBuiltin()) {
                $dependencyClass = new ReflectionClass($type->getName());

                if ($this->containerRegistry->has($dependencyClass->getName())) {
                    $dependency = $this->containerRegistry->resolve($dependencyClass->getName());
                    continue;
                } elseif ($type->allowsNull()) {
                    $dependency = null;
                    continue;
                } elseif ($dependencyClass->isInstantiable()) {
                    $dependency = $this->get($dependencyClass->getName());
                    continue;
                }
            } elseif ($dependency->isDefaultValueAvailable()) {
                $dependency = $dependency->getDefaultValue();
                continue;
            }

            throw new InvalidArgumentException(
                sprintf('Cannot resolve the dependency: %s', $name)
            );
        }
    }
}
