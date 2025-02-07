<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings;

use DragonCode\LaravelModelSettings\Models\Laravel;
use DragonCode\LaravelModelSettings\Services\Cache;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('model-settings')
            ->hasConfigFile()
            ->hasMigration('2025_02_05_230000_create_model_settings_table');
    }

    public function bootingPackage(): void
    {
        $this->bootMorphMap();
        $this->bootCache();
    }

    protected function bootMorphMap(): void
    {
        Relation::morphMap([
            'laravel' => Laravel::class,
        ]);
    }

    protected function bootCache(): void
    {
        $this->app->singleton(Cache::class, function (Application $app) {
            /** @var \Illuminate\Config\Repository $config */
            $config = $app['config'];

            return (new Cache())
                ->when((bool) $config->get('model-settings.cache.enabled'))
                ->ttl((int) $config->get('model-settings.cache.ttl'))
                ->hashKey((bool) $config->get('model-settings.cache.hash_keys'));
        });
    }
}
