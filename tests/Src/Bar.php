<?php

namespace Lumivel\Di\Test\Src;

class Bar
{
    public function __construct(public FooBar|null $fooBar = null) {}
}
