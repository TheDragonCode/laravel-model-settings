<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Services;

use DragonCode\LaravelModelSettings\Repositories\SettingsRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

use function blank;

class SettingsService
{
    public function __construct(
        protected Model $model,
        protected SettingsRepository $repository,
    ) {}

    public function all(): Collection
    {
        $defaults = $this->repository->all($this->defaultModel());
        $model    = $this->repository->all($this->model);

        return $defaults->replace($model);
    }

    public function get(int|string|UnitEnum $key): mixed
    {
        $value = $this->repository->get($this->model, $key);

        if (! blank($value)) {
            return $value;
        }

        return $this->repository->get($this->defaultModel(), $key);
    }

    public function set(int|string|UnitEnum $key, mixed $value): void
    {
        blank($value)
            ? $this->repository->delete($this->model, $key)
            : $this->repository->store($this->model, $key, $value);
    }

    public function forget(int|string|UnitEnum $key): void
    {
        $this->repository->delete($this->model, $key);
    }

    protected function defaultModel(): Model
    {
        $clone = $this->model->replicateQuietly(['id']);
        $clone->setAttribute($clone->getKeyName(), 0);

        return $clone;
    }
}
