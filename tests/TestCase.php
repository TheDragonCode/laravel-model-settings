<?php

namespace DragonCode\LaravelModelSettings\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

use function array_merge;
use function Orchestra\Testbench\workbench_path;

abstract class TestCase extends Orchestra
{
    use WithWorkbench;
    use RefreshDatabase;

    protected function shouldSeed(): bool
    {
        return true;
    }

    protected function defineEnvironment($app): void
    {
        $base = $app['config']->get('model-settings', []);
        $test = require workbench_path('config/model-settings.php');

        $app['config']->set('model-settings', array_merge($base, $test));
    }
}
