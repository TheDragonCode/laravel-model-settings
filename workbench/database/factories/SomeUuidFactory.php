<?php

declare(strict_types=1);

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Workbench\App\Models\SomeUuid;

class SomeUuidFactory extends Factory
{
    protected $model = SomeUuid::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid7(),
        ];
    }
}
