<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Services;

use DragonCode\LaravelModelSettings\Exceptions\BulkMutationException;
use DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast;
use DragonCode\LaravelModelSettings\Exceptions\InvalidSettingKey;
use DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException;
use DragonCode\LaravelModelSettings\Internal\SettingKey;
use DragonCode\LaravelModelSettings\Internal\SettingsScope;
use DragonCode\LaravelModelSettings\Repositories\SettingsRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Throwable;
use UnitEnum;

use function array_values;

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

    public function has(int|string|UnitEnum $key): bool
    {
        return $this->repository->has($this->scope, $key);
    }

    public function set(int|string|UnitEnum $key, mixed $value): void
    {
        $this->scope->ensureMutable();

        $this->repository->store($this->scope, $key, $value);

        $this->model->unsetRelation('modelSettings');
    }

    public function setMany(iterable $values): void
    {
        try {
            $this->scope->ensureMutable();

            $stored = $this->normalizeValues($values);

            $this->repository->storeMany($this->scope, $stored);
            $this->model->unsetRelation('modelSettings');
        } catch (InvalidPayloadCast|InvalidSettingKey|InvalidSettingsOwnerException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw BulkMutationException::setMany($this->model, $this->scope->isDefault(), $exception);
        }
    }

    public function forget(int|string|UnitEnum $key): void
    {
        $this->scope->ensureMutable();

        $this->repository->delete($this->scope, $key);
        $this->model->unsetRelation('modelSettings');
    }

    public function forgetMany(iterable $keys): void
    {
        try {
            $this->scope->ensureMutable();

            $normalized = $this->normalizeKeys($keys);

            $this->repository->deleteMany($this->scope, $normalized);
            $this->model->unsetRelation('modelSettings');
        } catch (InvalidPayloadCast|InvalidSettingKey|InvalidSettingsOwnerException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw BulkMutationException::forgetMany($this->model, $this->scope->isDefault(), $exception);
        }
    }

    public function purge(): void
    {
        try {
            $this->scope->ensureMutable();

            $this->repository->purge($this->scope);
            $this->model->unsetRelation('modelSettings');
        } catch (InvalidPayloadCast|InvalidSettingKey|InvalidSettingsOwnerException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw BulkMutationException::purge($this->model, $this->scope->isDefault(), $exception);
        }
    }

    protected function normalizeValues(iterable $values): array
    {
        $normalized = [];

        foreach ($values as $key => $value) {
            $normalized[SettingKey::normalize($key)] = $value;
        }

        return $normalized;
    }

    protected function normalizeKeys(iterable $keys): array
    {
        $normalized = [];

        foreach ($keys as $key) {
            $value              = SettingKey::normalize($key);
            $normalized[$value] = $value;
        }

        return array_values($normalized);
    }
}
