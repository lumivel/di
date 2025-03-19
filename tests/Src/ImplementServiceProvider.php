<?php

namespace Lumivel\Di\Test\Src;

use Lumivel\Di\Concerns\ServiceProvider\AbstractProvider;

class ImplementServiceProvider extends AbstractProvider
{
    public function register(): void {}

    public function injectIt() { return 'injected'; }
}
