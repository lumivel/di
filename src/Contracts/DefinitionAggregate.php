<?php

declare(strict_types=1);

namespace Lumivel\Di\Contracts;
/**
 * DefinitionAggregate interface
 *
 * This interface defines the contract for a aggregate definition that is aware
 * of a container, and manage services definition by their identifiers.
 */
interface DefinitionAggregate extends ContainerAware
{
    /**
     * Adds a definition to the aggregate.
     *
     * @param string $identifier The identifier for the definition.
     * @param mixed|null $concrete The concrete implementation of the service.
     *
     * @return Definition The added definition instance.
     */
    public function add(string $identifier, $concrete = null): Definition;

    /**
     * Retrieves a definition by its identifier.
     *
     * @param string $identifier The identifier of the definition.
     *
     * @return Definition The retrieved definition.
     * @throws \Lumivel\Di\Throws\ContainerException If the service cannot be
     * resolved
     * @throws \Lumivel\Di\Throws\NotFoundException If the definition cannot be
     * found
     */
    public function get(string $identifier): Definition;

    /**
     * Checks whether a definition bound with the given identifier.
     *
     * @param mixed $identifier The identifier of the definition.
     *
     * @return bool True if the definition bound, false otherwise.
     */
    public function has($identifier): bool;

    /**
     * Resolves a definition bound to the given identifier.
     *
     * @param mixed $identifier The identifier of the definition.
     * @param bool $new Whether to resolve a new instance.
     *
     * @return mixed The resolved service.
     * @throws \Lumivel\Di\Throws\ContainerException If the service cannot be
     * resolved
     * @throws \Lumivel\Di\Throws\NotFoundException If the definition cannot be
     * found
     */
    public function resolve($identifier, bool $new = false): mixed;
}
