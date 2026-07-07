<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Services;

use DragonCode\LaravelModelSettings\Storages\DefaultStorage;
use DragonCode\LaravelModelSettings\Storages\ModelStorage;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

use function array_merge;
use function blank;

class SettingsService
{
    public function __construct(
        protected Model $model,
        protected ModelStorage $modelStorage,
        protected DefaultStorage $defaultStorage,
    ) {}

    public function all(): array
    {
        $defaults = $this->defaultStorage->all();
        $model    = $this->modelStorage->all($this->model);

        return array_merge($defaults->toArray(), $model->toArray());
    }

    public function get(UnitEnum|string $key): mixed
    {
        $value = $this->modelStorage->get($this->model, $key);

        if (! blank($value)) {
            return $value;
        }

        return $this->defaultStorage->get($key);
    }

    public function set(UnitEnum|string $key, mixed $value): void
    {
        $this->modelStorage->set($this->model, $key, $value);
    }

    public function forget(UnitEnum|string $key): void
    {
        $this->modelStorage->forget($this->model, $key);
    }
}
