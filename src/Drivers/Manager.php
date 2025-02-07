<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Drivers;

use Illuminate\Support\Manager as Base;

/**
 * @method Driver driver($driver = null)
 */
class Manager extends Base
{
    public function getDefaultDriver(): string
    {
        return 'database';
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createDatabaseDriver(): Driver
    {
        return $this->getContainer()->make(Database::class);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createRedisDriver(): Driver
    {
        return $this->getContainer()->make(Redis::class);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createFileDriver(): Driver
    {
        return $this->getContainer()->make(File::class);
    }
}
