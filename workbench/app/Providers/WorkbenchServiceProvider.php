<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Workbench\Database\Seeders\DatabaseSeeder;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('DatabaseSeeder', DatabaseSeeder::class);
    }
}
