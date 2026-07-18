<?php

declare(strict_types=1);

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\SomeString;

class SomeStringFactory extends Factory
{
    protected $model = SomeString::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->bothify('model-########-????'),
        ];
    }
}
