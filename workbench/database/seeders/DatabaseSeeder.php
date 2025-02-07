<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        UserFactory::new()->count(2)->create();
    }
}
