<?php

declare(strict_types=1);

namespace Lumivel\Di\ServiceProvider;

use Lumivel\Di\Concerns\WithContainerAware;
use Lumivel\Di\Contracts\ServiceProvider\Provider as ProviderContract;
use Lumivel\Di\Contracts\ServiceProvider\ProviderAggregate as ProviderAggregateContract;
use Lumivel\Di\Throws\ContainerException;
use Traversable;

class ProviderAggregate implements ProviderAggregateContract
{
    use WithContainerAware;

    /**
     * Service providers
     *
     * @var \Lumivel\Di\Contracts\ServiceProvider\Provider[]
     */
    protected array $providers = [];

    /**
     * Registered providers
     *
     * @var array
     */
    protected array $registered = [];

    public function add(ProviderContract $provider): static
    {
        if (!$this->exists($provider)) {
            $provider->setContainer($this->getContainer());
            $this->providers[] = $provider;
        }

        return $this;
    }

    public function canProvide(string $identifier): bool
    {
        foreach ($this->getIterator() as $provider) {
            if ($provider->canProvides($identifier)) {
                return true;
            }
        }
        return false;
    }

    public function countProviders(): int
    {
        return count($this->providers);
    }

    public function exists(ProviderContract $serviceProvider): bool
    {
        foreach ($this->getIterator() as $provider) {
            if ($provider->getIdentifier() === $serviceProvider->getIdentifier()) {
                return true;
            }
        }

        return false;
    }

    public function getIterator(): Traversable
    {
        yield from $this->providers;
    }

    public function register(string $identifier): void
    {
        if (false === $this->canProvide($identifier)) {
            throw new ContainerException(
                "Service ($identifier) is not provided by any provider."
            );
        }

        foreach ($this->getIterator() as $provider) {
            if (in_array($provider->getIdentifier(), $this->registered, true)) {
                continue;
            }

            if ($provider->canProvides($identifier)) {
                $provider->register();
                $this->registered[] = $provider->getIdentifier();
            }
        }
    }
}
