<?php

declare(strict_types=1);

namespace Ray\Bindings;

use PHPUnit\Framework\TestCase;

final class BindingsTest extends TestCase
{
    protected Bindings $bindings;

    protected function setUp(): void
    {
        $this->bindings = new Bindings();
    }

    public function testIsInstanceOfBindings(): void
    {
        $actual = $this->bindings;
        $this->assertInstanceOf(Bindings::class, $actual);
    }
}
