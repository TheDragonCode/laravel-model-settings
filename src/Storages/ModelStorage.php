<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Storages;

use DragonCode\LaravelModelSettings\Repositories\SettingsRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

readonly class ModelStorage
{
    public function __construct(
        protected SettingsRepository $repository,
    ) {}

    public function all(Model $model): Collection
    {
        return $this->repository->all(
            type: $model->getMorphClass(),
            id  : $model->getKey(),
        );
    }

    public function get(Model $model, UnitEnum|string $key): mixed
    {
        return $this->repository->get(
            type: $model->getMorphClass(),
            id  : $model->getKey(),
            key : $key,
        );
    }

    public function set(Model $model, UnitEnum|string $key, mixed $value): Model
    {
        return $this->repository->store(
            type : $model->getMorphClass(),
            id   : $model->getKey(),
            key  : $key,
            value: $value,
        );
    }

    public function forget(Model $model, UnitEnum|string $key): void
    {
        $this->repository->delete(
            type: $model->getMorphClass(),
            id  : $model->getKey(),
            key : $key,
        );
    }
}
