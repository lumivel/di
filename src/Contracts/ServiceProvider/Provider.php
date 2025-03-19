<?php

declare(strict_types=1);

namespace Lumivel\Di\Contracts\ServiceProvider;

use Lumivel\Di\Contracts\ContainerAware;

/**
 * Provider interface
 *
 * The provider interface defines the contract for registering and resolving
 * group of services
 */
interface Provider extends ContainerAware
{
    /**
     * Check whether the provider can provides a service with the given
     * identifier.
     *
     * @param string $identifier The identifier of the service
     *
     * @return bool
     */
    public function canProvides(string $identifier): bool;

    /**
     * Get the identifier of this provider
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Register the services provided by this provider to the container
     * registry.
     *
     * @return void
     */
    public function register(): void;
}
