<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Drivers;

use Illuminate\Contracts\Support\Arrayable;

class Redis extends Driver
{
    public function apply(array|Arrayable $settings): static
    {
        // TODO: Implement apply() method.
    }

    public function clear(): static
    {
        // TODO: Implement clear() method.
    }

    protected function getPayload(): array|Arrayable
    {
        // TODO: Implement getPayload() method.
    }
}
