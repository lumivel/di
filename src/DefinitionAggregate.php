<?php

declare(strict_types=1);

namespace Lumivel\Di;

class DefinitionAggregate implements Contracts\DefinitionAggregate
{
    use Concerns\WithContainerAware;

    /**
     * @var \Lumivel\Di\Contracts\Definition[]
     */
    protected array $definitions;

    public function __construct(array $definitions = [])
    {
        $this->definitions = $definitions;
    }

    public function add(string $identifier, $concrete = null): Contracts\Definition
    {
        $concrete ??= $identifier;
        $this->definitions[$identifier] = $definition = new Definition($concrete);
        return $definition;
    }

    public function get(string $identifier): Contracts\Definition
    {
        if ($this->has($identifier)) {
            $definition = $this->definitions[$identifier];

            $definition->setContainer($this->getContainer());

            return $definition;
        }

        throw new Throws\NotFoundException(
            sprintf("Service (%s) is not a definition.", $identifier)
        );
    }

    public function has($identifier): bool
    {
        return array_key_exists($identifier, $this->definitions);
    }

    public function resolve($identifier, bool $new = false): mixed
    {
        return $this->get($identifier)->make($new);
    }
}
