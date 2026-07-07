<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Services;

use DragonCode\LaravelModelSettings\Storages\ModelStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

use function blank;

class SettingsService
{
    public function __construct(
        protected Model $model,
        protected ModelStorage $modelStorage,
    ) {}

    public function all(): Collection
    {
        $defaults = $this->modelStorage->all($this->defaultModel());
        $model    = $this->modelStorage->all($this->model);

        return $defaults->merge($model);
    }

    public function get(UnitEnum|string $key): mixed
    {
        $value = $this->modelStorage->get($this->model, $key);

        if (! blank($value)) {
            return $value;
        }

        return $this->modelStorage->get($this->defaultModel(), $key);
    }

    public function set(UnitEnum|string $key, mixed $value): void
    {
        $this->modelStorage->set($this->model, $key, $value);
    }

    public function forget(UnitEnum|string $key): void
    {
        $this->modelStorage->forget($this->model, $key);
    }

    protected function defaultModel(): Model
    {
        $clone = $this->model->replicateQuietly(['id']);
        $clone->setAttribute($clone->getKeyName(), 0);

        return $clone;
    }
}
