<?php

declare(strict_types=1);

namespace Lumivel\Di\Contracts\Definition;

/**
 * Service definition argument interface
 */
interface Argument
{
    /**
     * Get argument value
     *
     * @return mixed Argument value
     */
    public function get(): mixed;
}
