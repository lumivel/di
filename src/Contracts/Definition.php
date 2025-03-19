<?php

declare(strict_types=1);

namespace Lumivel\Di\Contracts;

/**
 * Service definition interface
 *
 * This interface defines the contract for a definition in the dependency
 * injection container. It provides a method to add arguments for service
 * constructor and method calls after resolving the definition concrete.
 */
interface Definition extends ContainerAware
{
    /**
     * Adds a constructor argument to the definition.
     *
     * @param string|\Lumivel\Di\Contracts\Definition\Argument $arg The
     * argument to add.
     *
     * @return \Lumivel\Di\Contracts\Definition The current definition instance.
     */
    public function addArgument(Definition\Argument|string $arg): Definition;

    /**
     * Adds multiple constructor arguments to the definition.
     *
     * @param string[]|\Lumivel\Di\Contracts\Definition\Argument[] $args The
     * arguments to add.
     *
     * @return \Lumivel\Di\Contracts\Definition The current definition instance.
     */
    public function addArguments(array $args): Definition;

    /**
     * Adds a method call to the definition after resolving service.
     *
     * @param string $method The service method name.
     * @param array|null $params The parameters to pass for the method call.
     *
     * @return \Lumivel\Di\Contracts\Definition The current definition instance.
     */
    public function injectMethodCall(string $method, array|null $params = null): static;

    /**
     * Check whether the service is shared.
     *
     * @return bool `true` if the service is shared, `false` otherwise.
     */
    public function isShared(): bool;

    /**
     * Resolve the service for this definition.
     *
     * @param bool $new Whether to force resolving of a service, instead of using
     * shared service
     *
     * @return mixed The resolved service
     */
    public function make(bool $new = false): mixed;

    /**
     * Sets whether the definition is shared.
     *
     * @param bool $value When set to `true` the service is shared
     *
     * @return \Lumivel\Di\Contracts\Definition The current definition.
     */
    public function setShared(bool $value = true): static;
}
