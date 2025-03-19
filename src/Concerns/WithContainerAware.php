<?php

declare(strict_types=1);

namespace Lumivel\Di\Concerns;

use Lumivel\Di\Contracts\ContainerRegistry;

/**
 * WithContainerAware Trait
 *
 * Provides definition for getting and setting a container registry.
 */
trait WithContainerAware
{
    /**
     * The container registry instance.
     *
     * @var \Lumivel\Di\Contracts\ContainerRegistry|null The container registry.
     */
    protected ContainerRegistry|null $containerRegistry = null;

    /**
     * Get the container registry instance.
     *
     * @return ContainerRegistry The container registry instance.
     */
    public function getContainer(): ContainerRegistry
    {
        return $this->containerRegistry;
    }

    /**
     * Set the container registry instance.
     *
     * @param ContainerRegistry $container The container registry instance.
     *
     * @return static The current implementation instance for method chaining.
     */
    public function setContainer(ContainerRegistry $container): static
    {
        $this->containerRegistry = $container;
        return $this;
    }
}
