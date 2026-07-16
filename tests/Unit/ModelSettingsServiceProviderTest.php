<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\ModelSettingsServiceProvider;
use Illuminate\Contracts\Foundation\Application;

test('skips publish registration outside console', function (): void {
    $app = Mockery::mock(Application::class);

    $app->shouldReceive('runningInConsole')->once()->andReturnFalse();
    $app->shouldNotReceive('configPath');
    $app->shouldNotReceive('databasePath');

    (new ModelSettingsServiceProvider($app))->boot();
});
