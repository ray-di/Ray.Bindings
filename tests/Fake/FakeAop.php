<?php

declare(strict_types=1);

namespace Ray\Bindings\Fake;

class FakeAop implements FakeAopInterface
{
    public function returnSame(int $value): int
    {
        return $value;
    }
}
