<?php

declare(strict_types=1);

namespace Workbench\App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SomeData extends Data
{
    public function __construct(
        public string $foo,
        public string $bar,
        public Optional|string $baz,
        public SingleItemData $item,
        #[DataCollectionOf(CollectionItemData::class)]
        public Collection $collection,
    ) {}
}
