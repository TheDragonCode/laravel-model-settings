<?php

declare(strict_types=1);

namespace Workbench\App\Data;

use Spatie\LaravelData\Data;

class CollectionItemData extends Data
{
    public function __construct(
        public int $id,
        public string $comment,
    ) {}
}
