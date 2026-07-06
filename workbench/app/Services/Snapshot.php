<?php

declare(strict_types=1);

namespace Workbench\App\Services;

use function json_encode;

class Snapshot
{
    protected int $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

    public function __construct(
        protected array|int|string|null $value,
    ) {}

    public function toSnapshot(): string
    {
        return json_encode($this->value, $this->flags);
    }
}
