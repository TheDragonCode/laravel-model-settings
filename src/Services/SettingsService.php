<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Services;

use DragonCode\LaravelModelSettings\Internal\SettingsScope;
use DragonCode\LaravelModelSettings\Repositories\SettingsRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

use function blank;

class SettingsService
{
    protected SettingsScope $scope;

    public function __construct(
        protected Model $model,
        protected SettingsRepository $repository,
        bool $defaultScope = false,
    ) {
        $this->scope = $defaultScope
            ? SettingsScope::defaults($this->model)
            : SettingsScope::model($this->model);
    }

    public function all(): Collection
    {
        return $this->repository->all($this->scope);
    }

    public function get(int|string|UnitEnum $key): mixed
    {
        return $this->repository->get($this->scope, $key);
    }

    public function set(int|string|UnitEnum $key, mixed $value): void
    {
        $this->scope->ensureMutable();

        blank($value)
            ? $this->repository->delete($this->scope, $key)
            : $this->repository->store($this->scope, $key, $value);

        $this->model->unsetRelation('modelSettings');
    }

    public function forget(int|string|UnitEnum $key): void
    {
        $this->scope->ensureMutable();

        $this->repository->delete($this->scope, $key);
        $this->model->unsetRelation('modelSettings');
    }
}
