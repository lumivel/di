<?php

declare(strict_types=1);

namespace Lumivel\Di\Concerns;

use Lumivel\Di\Contracts\ContainerRegistry;
use Lumivel\Di\Contracts\Definition\Argument;
use Lumivel\Di\Throws\InvalidArgumentException;

/**
 * WithArgumentResolver Trait
 *
 * Provides methods to resolve arguments using a dependency injection container.
 */
trait WithArgumentResolver
{
    /**
     * Resolves a single argument from the provided container.
     *
     * @param \Lumivel\Di\Contracts\ContainerRegistry $container The dependency
     * injection container.
     * @param mixed $arg The argument to resolve.
     *
     * @return void
     * @throws \Lumivel\Di\Throws\InvalidArgumentException
     */
    public function resolveArgument(ContainerRegistry $container, &$arg): void
    {
        if (is_string($arg)) {
            $arg = $container->resolve($arg);
        } elseif ($arg instanceof Argument) {
            $arg = $arg->get();
        } else {
            $argType = Argument::class;
            $foundType = gettype($arg);
            throw new InvalidArgumentException(
                "Invalid argument type expected string or $argType found ($foundType)"
            );
        }
    }

    /**
     * Resolves an array of arguments using the container.
     *
     * @param array $args The array of arguments to resolve.
     * @return array The array of resolved arguments.
     */
    public function resolveArguments(array $args)
    {
        $container = $this->getContainer();

        foreach ($args as &$arg) {
            $this->resolveArgument($container, $arg);
        }

        return $args;
    }

    abstract public function getContainer(): ContainerRegistry;
}
