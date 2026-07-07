<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Storages;

use DragonCode\LaravelModelSettings\Constants\DefaultConstant;
use DragonCode\LaravelModelSettings\Repositories\SettingsRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

readonly class DefaultStorage
{
    public function __construct(
        protected SettingsRepository $repository,
    ) {}

    public function all(): Collection
    {
        return $this->repository->all(
            type: DefaultConstant::Type,
            id  : DefaultConstant::Id,
        );
    }

    public function get(UnitEnum|string $key): mixed
    {
        return $this->repository->get(
            type: DefaultConstant::Type,
            id  : DefaultConstant::Id,
            key : $key
        );
    }

    public function set(UnitEnum|string $key, mixed $value): Model
    {
        return $this->repository->store(
            type : DefaultConstant::Type,
            id   : DefaultConstant::Id,
            key  : $key,
            value: $value,
        );
    }

    public function forget(UnitEnum|string $key): void
    {
        $this->repository->delete(
            type: DefaultConstant::Type,
            id  : DefaultConstant::Id,
            key : $key,
        );
    }
}
