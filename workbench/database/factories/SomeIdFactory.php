<?php

declare(strict_types=1);

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\SomeId;

class SomeIdFactory extends Factory
{
    protected $model = SomeId::class;

    public function definition(): array
    {
        return [];
    }
}
