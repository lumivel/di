<?php

namespace Lumivel\Di\Test\Src;

class Foo implements IFoo
{
    public function __construct(public Bar|null $bar = null) {}

    public function someMethod() {}
}
