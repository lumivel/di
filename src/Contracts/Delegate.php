<?php

declare(strict_types=1);

namespace Lumivel\Di\Contracts;
/**
 * Interface Delegate
 *
 * The Delegate is responsible for the delegation of services to the container
 * registry that violate the primary DI container registry activities.
 */
interface Delegate extends Container, ContainerAware {}
