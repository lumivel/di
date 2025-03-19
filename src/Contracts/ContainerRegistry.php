<?php

declare(strict_types=1);

namespace Lumivel\Di\Contracts;

/**
 * ContainerRegistry interface
 *
 * This interface defines the contracts for a container registry.
 * Implementations of this interface are responsible for managing registering,
 * and resolving a service and its dependencies within the implementing
 * container.
 */
interface ContainerRegistry extends Container
{
    /**
     * Adds a new service definition to the container registry.
     *
     * @param string $identifier The unique identifier for the of service.
     * @param mixed $concrete The concrete implementation the service.
     *
     * @return \Lumivel\Di\Contracts\Definition The definition related to
     * the specified identifier.
     */
    public function add(string $identifier, $concrete = null): Definition;

    /**
     * Adds a delegate container to the registry.
     *
     * @param \Lumivel\Di\Contracts\Delegate $delegate The container to be
     * added as a delegate.
     * @param bool $sign Specifies whether this deledate should be signed
     *
     * @return null|string Returns the delegate signature if the second
     * parameter is true otherwise `null`.
     */
    public function addDelegate(Delegate $delegate, bool $sign = false): null|string;

    /**
     * Add a services provider to the registry.
     *
     * @param \Lumivel\Di\Contracts\ServiceProvider\Provider $provider
     * @param bool $sign Specifies whether this provider should be signed
     *
     * @return null|string Returns the provider signature if the second
     * parameter is true otherwise `null`.
     */
    public function addProvider(ServiceProvider\Provider $provider, bool $sign = false): null|string;

    /**
     * Add a shared definition to the container registry.
     *
     * @param string $identifier The unique identifier for the of service.
     * @param mixed|null $concrete The concrete implementation the service.
     *
     * @return Definition The definition related to specified identifier.
     */
    public function addShared(string $identifier, $concrete = null): Definition;

    /**
     * Inject a method or an instance into the container for later usage.
     *
     * @param string $signature The delegate or service provider signature.
     * @param string $name The name used to call the method or instance.
     * @param string $type **method** (default) binds a callable method,
     * **object** binds the instance
     *
     * @return void
     * @throws \Lumivel\Di\Throws\NotFoundException
     */
    public function inject(string $signature, string $name, string $type = 'method'): void;

    /**
     * Inject an instance into container
     *
     * @param string $signature The delegate or service provider signature.
     * @param string $name The name used to call the method or instance.
     *
     * @return void
     */
    public function injectInstance(string $signature, string $name);

    /**
     * Inject a method into container
     *
     * @param string $signature The delegate or service provider signature.
     * @param string $name The name used to call the method or instance.
     *
     * @return void
     */
    public function injectMethod(string $signature, string $name);

    /**
     * Modify an existing definition in the container registry.
     *
     * @param string $identifier The identifier for the definition to modify.
     *
     * @return Definition The definition instance.
     */
    public function modify(string $identifier): Definition;

    /**
     * Resolve a definition from the container registry.
     *
     * @param mixed $identifier The identifier of the service to resolve.
     * @param bool $new Whether to create a new instance or not
     *
     * @return mixed The resolved service.
     */
    public function resolve($identifier, bool $new = false): mixed;

    /**
     * Calls the injected name to container (Provider, Delegate) using its
     * signature.
     *
     * @param string $name The call name bound to the signature
     * @param array $parameters The parameters to pass when invoking call
     *
     * @return mixed
     * @throws \Lumivel\Di\Throws\NotFoundException
     */
    public function __call(string $name, array $parameters = []): mixed;
}
