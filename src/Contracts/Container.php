<?php

declare(strict_types=1);

namespace Lumivel\Di\Contracts;

/**
 * Interface for a dependency injection container.
 * 
 * This interface defines the contract for a dependency injection (DI) container,
 * which is responsible for managing the lifecycle and resolution of dependencies
 * within an application. A DI container allows for the registration, resolution,
 * and management of object dependencies, promoting loose coupling and enhancing
 * testability and maintainability of the codebase.
 * 
 */
interface Container
{
    /**
     * Get the service bound with the specified identifier.
     *
     * @param string $identifier The identifier the service to retrieve.
     *
     * @return mixed The service bound to the identifier
     * @throws \Lumivel\Di\Throws\ContainerException When error occurs while trying to get the service.
     * @throws \Lumivel\Di\Throws\NotFoundException When the service cannot be found.
     */
    public function get(string $identifier): mixed;

    /**
     * Check whether DI has a service bound to the given identifier.
     *
     * @param string $identifier The identifier check if it is bound.
     *
     * @return bool
     */
    public function has(string $identifier): bool;
}
