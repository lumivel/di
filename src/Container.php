<?php

declare(strict_types=1);

namespace Lumivel\Di;

class Container implements Contracts\ContainerRegistry
{
    /**
     * Aggregate definitions
     *
     * @var \Lumivel\Di\Contracts\DefinitionAggregate
     */
    protected Contracts\DefinitionAggregate $definitions;

    /**
     * Delegates containers
     *
     * @var \Lumivel\Di\Contracts\Delegate[]
     */
    protected array $delegates;

    /**
     * Injected components access
     *
     * @var array
     */
    protected array $injectedComponents;

    /**
     * Service providers
     *
     * @var \Lumivel\Di\Contracts\ServiceProvider\ProviderAggregate
     */
    protected Contracts\ServiceProvider\ProviderAggregate|null $providers;

    /**
     * Signature of Containers (Delegate | Providers)
     */
    protected array $signatures;

    public function __construct(
        Contracts\DefinitionAggregate|null $definitions = null,
        Contracts\ServiceProvider\ProviderAggregate|null $providers = null,
    ) {
        $this->definitions = $definitions ?? new DefinitionAggregate();
        $this->providers = $providers ?? new ServiceProvider\ProviderAggregate;
        $this->delegates = [];
        $this->signatures = [];
        $this->injectedComponents = [];

        $this->definitions->setContainer($this);
        $this->providers->setContainer($this);
    }

    public function add(string $identifier, $concrete = null): Contracts\Definition
    {
        return $this->definitions->add($identifier, $concrete);
    }

    public function addDelegate(Contracts\Delegate $delegate, bool $sign = false): null|string
    {
        $this->delegates[] = $delegate;

        if ($sign) {
            return $this->addSignature($delegate);
        }

        if ($delegate instanceof Contracts\ContainerAware) {
            $delegate->setContainer($this);
        }

        return null;
    }

    public function addProvider(Contracts\ServiceProvider\Provider $provider, bool $sign = false): null|string
    {
        $this->providers->add($provider);

        if ($sign) {
            return $this->addSignature($provider);
        }

        return null;
    }

    public function addShared(string $identifier, $concrete = null): Contracts\Definition
    {
        return $this->add($identifier, $concrete)->setShared();
    }

    protected function addSignature(
        Contracts\Delegate|Contracts\ServiceProvider\Provider $container
    ): string {
        $type = ($container instanceof Contracts\Delegate) ? 'delegate' : 'provider';
        $count = $type === 'delegate'
        ? count($this->delegates)
        : $this->providers->countProviders();
        $id = md5($container::class . $count);
        $this->signatures[$id] = [
            'type' => $type,
            'index' => --$count
        ];

        return $id;
    }

    public function get(string $identifier): mixed
    {
        return $this->getService($identifier);
    }

    /**
     * Get the definition for the given identifier.
     *
     * @param string $identifier The identifier of the service
     *
     * @return \Lumivel\Di\Contracts\Definition|mixed The retrieved service
     * @throws \Lumivel\Di\Throws\NotFoundException
     */
    protected function getService(string $identifier): mixed
    {
        if ($this->definitions->has($identifier)) {
            return $this->definitions->get($identifier);
        }

        if ($this->providers->canProvide($identifier)) {
            $this->providers->register($identifier);

            if (!$this->definitions->has($identifier)) {
                throw new Throws\ContainerException(sprintf(
                    "Service provider lied to provides (%s) service",
                    $identifier
                ));
            }

            return $this->getService($identifier);
        }

        foreach ($this->delegates as $delegate) {
            if ($delegate->has($identifier)) {
                return $delegate->get($identifier);
            }
        }

        throw new Throws\NotFoundException(sprintf(
            "Service (%s) not found",
            $identifier
        ));
    }

    public function has(string $identifier): bool
    {
        if ($this->definitions->has($identifier)) {
            return true;
        }

        if ($this->providers->canProvide($identifier)) {
            return true;
        }

        foreach ($this->delegates as $delegate) {
            if ($delegate->has($identifier)) {
                return true;
            }
        }

        return false;
    }

    public function inject(string $signature, string $name, string $type = 'method'): void
    {
        if (array_key_exists($signature, $this->signatures)) {
            $this->injectedComponents[$name] = [
                'sign' => $signature,
                'type' => $type
            ];

            return;
        }

        throw new Throws\ContainerException("Signature ($signature) is not valid");
    }

    public function injectInstance(string $signature, string $name)
    {
        $this->inject($signature, $name, 'object');
    }

    public function injectMethod(string $signature, string $name)
    {
        $this->inject($signature, $name, 'method');
    }

    public function modify(string $identifier): Contracts\Definition
    {
        if ($this->definitions->has($identifier)) {
            return $this->definitions->get($identifier);
        }

        throw new Throws\NotFoundException(sprintf(
            "Service (%s) can not be modified, as it is not found",
            $identifier
        ));
    }

    public function resolve($identifier, bool $new = false): mixed
    {
        $service = $this->getService($identifier);

        return $service instanceof Contracts\Definition
            ? $service->make($new)
            : $service;
    }

    public function __call(string $name, array $parameters = []): mixed
    {
        if (array_key_exists($name, $this->injectedComponents)) {
            $component = $this->injectedComponents[$name];
            $signature = $this->signatures[$component['sign']];

            if ($signature['type'] === 'delegate') {
                $instance = $this->delegates[$signature['index']];
            } else {
                foreach ($this->providers->getIterator() as $index => $provider) {
                    if ($index === $signature['index']) {
                        $instance = $provider;
                        break;
                    }
                }
            }

            if (isset($instance)) {
                return $component['type'] === 'method'
                ? $instance->{$name}(...$parameters) : $instance;
            }

            throw new Throws\NotFoundException(
                "Signature for call ($name) not found"
            );
        }

        throw new Throws\ContainerException("Calling ($name) not injected.");
    }
}
