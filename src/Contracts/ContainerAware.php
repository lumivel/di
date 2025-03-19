<?php

declare(strict_types=1);

namespace Lumivel\Di\Contracts;

/**
 * ContainerAware interface
 *
 * This interface defines the contracts for getting and setting a container
 * registry. Implementing concretes should be aware of a container registry,
 * enabling them to aware of DI container registry, so it can retrieve and
 * assign a service dependency to it.
 */
interface ContainerAware
{
    /**
     * Get the container registry instance.
     *
     * @return \Lumivel\Di\Contracts\ContainerRegistry The container registry
     * instance.
     */
    public function getContainer(): ContainerRegistry;

    /**
     * Set the container registry instance.
     *
     * @param \Lumivel\Di\Contracts\ContainerRegistry $container
     *
     * @return static
     */
    public function setContainer(ContainerRegistry $container): static;
}
