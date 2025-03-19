<?php

declare(strict_types=1);

namespace Lumivel\Di\Delegates;

use Lumivel\Di\Concerns\WithContainerAware;
use Lumivel\Di\Contracts\Delegate;
use Lumivel\Di\Throws\NotFoundException;

/**
 * ParameterResolver class delegate.
 *
 * This delegate enables container registry to resolve parameters. It is
 * responsible for the delegation of parameters that are not registered to the
 * container registry.
 */
class ParameterResolver implements Delegate
{
    use WithContainerAware;

    protected array $parameters = [];

    /**
     * Add a parameter to the resolver.
     *
     * @param string $identifier The unique identifier for the parameter.
     * @param mixed $value The value of the parameter.
     *
     * @return static The current instance of the parameter resolver.
     */
    public function addParameter(string $identifier, $value): static
    {
        $this->parameters[$identifier] = $value;
        return $this;
    }

    public function get(string $identifier): mixed
    {
        if ($this->has($identifier)) {
            return $this->parameters[$identifier];
        }

        throw new NotFoundException("Parameter $identifier not found");
    }

    public function has(string $identifier): bool
    {
        return array_key_exists($identifier, $this->parameters);
    }
}
