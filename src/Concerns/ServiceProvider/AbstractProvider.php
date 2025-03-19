<?php

declare(strict_types=1);

namespace Lumivel\Di\Concerns\ServiceProvider;

use Lumivel\Di\Concerns\WithContainerAware;
use Lumivel\Di\Contracts\ServiceProvider\Provider;

abstract class AbstractProvider implements Provider
{
    use WithContainerAware;

    protected string $identifier;

    public function getIdentifier(): string
    {
        return $this->identifier ??= get_class($this);
    }

    public function canProvides(string $identifier): bool
    {
        return false;
    }
}
