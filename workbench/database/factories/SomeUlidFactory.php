<?php

declare(strict_types=1);

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Workbench\App\Models\SomeUlid;

class SomeUlidFactory extends Factory
{
    protected $model = SomeUlid::class;

    public function definition(): array
    {
        return [
            'ulid' => strtolower((string) Str::ulid()),
        ];
    }
}
