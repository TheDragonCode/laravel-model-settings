<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Storages;

use BackedEnum;
use DragonCode\LaravelModelSettings\Repositories\SettingsRepository;
use Illuminate\Database\Eloquent\Model;

readonly class ModelStorage
{
    public function __construct(
        protected SettingsRepository $repository,
    ) {}

    public function store(Model $model, BackedEnum|string $key, mixed $value): Model
    {
        return $this->repository->store(
            type : $model->getMorphClass(),
            id   : $model->getKey(),
            key  : $key,
            value: $value,
        );
    }

    public function delete(Model $model, BackedEnum|string $key): void
    {
        $this->repository->delete(
            type: $model->getMorphClass(),
            id  : $model->getKey(),
            key : $key,
        );
    }
}
