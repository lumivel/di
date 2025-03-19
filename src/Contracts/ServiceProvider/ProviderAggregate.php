<?php

declare(strict_types=1);

namespace Lumivel\Di\Contracts\ServiceProvider;

use IteratorAggregate;
use Lumivel\Di\Contracts\ContainerAware;
use Traversable;

/**
 * ProviderAggregate interface
 */
interface ProviderAggregate extends ContainerAware, IteratorAggregate
{
    /**
     * Adds a provider to the aggregate.
     *
     * @param \Lumivel\Di\Contracts\ServiceProvider\Provider $provider
     *
     * @return static
     */
    public function add(Provider $provider): static;

    /**
     * Check whether providers can provider the service by the given identifier.
     *
     * @param string $identifier The identifier of the service.
     *
     * @return bool
     */
    public function canProvide(string $identifier): bool;

    /**
     * Get the total number of providers added.
     *
     * @return int
     */
    public function countProviders(): int;

    /**
     * Returns providers as iterator
     *
     * @return \Traversable<int, \Lumivel\Di\Concerns\ServiceProvider\AbstractProvider>
     */
    public function getIterator(): Traversable;

    /**
     * Register the provider that provides service by the given identifier.
     *
     * @param string $identifier The identifier of the service.
     *
     * @return void
     */
    public function register(string $identifier): void;
}
