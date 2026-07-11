<?php

declare(strict_types=1);

namespace Workbench\App\Data;

use Spatie\LaravelData\Data;

class SingleItemData extends Data
{
    public function __construct(
        public string $firstName,
        public string $lastName,
    ) {}
}
