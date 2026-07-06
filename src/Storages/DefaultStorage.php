<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Storages;

use BackedEnum;
use DragonCode\LaravelModelSettings\Constants\DefaultConstant;
use DragonCode\LaravelModelSettings\Repositories\SettingsRepository;
use Illuminate\Database\Eloquent\Model;

readonly class DefaultStorage
{
    public function __construct(
        protected SettingsRepository $repository,
    ) {}

    public function store(BackedEnum|string $key, mixed $value): Model
    {
        return $this->repository->store(
            type : DefaultConstant::Type,
            id   : DefaultConstant::Id,
            key  : $key,
            value: $value,
        );
    }

    public function delete(BackedEnum|string $key): void
    {
        $this->repository->delete(
            type: DefaultConstant::Type,
            id  : DefaultConstant::Id,
            key : $key,
        );
    }
}
