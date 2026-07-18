<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Services;

use DragonCode\LaravelModelSettings\Internal\SettingKey;
use DragonCode\LaravelModelSettings\Internal\SettingsScope;
use DragonCode\LaravelModelSettings\Repositories\SettingsRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;
use UnitEnum;

use function array_values;
use function count;

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
        $operation = 'setMany';

        Log::debug('Model settings bulk mutation started.', $this->logContext($operation));

        try {
            $this->scope->ensureMutable();

            $stored = $this->normalizeValues($values);

            Log::debug('Model settings bulk mutation prepared.', $this->logContext($operation, [
                'stored_count' => count($stored),
            ]));

            $this->repository->storeMany($this->scope, $stored);
            $this->model->unsetRelation('modelSettings');

            Log::debug('Model settings bulk mutation completed.', $this->logContext($operation, [
                'stored_count' => count($stored),
            ]));
        } catch (Throwable $exception) {
            Log::error('Model settings bulk mutation failed.', $this->logContext($operation, [
                'exception' => $exception::class,
            ]));

            throw $exception;
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
        $operation = 'forgetMany';

        Log::debug('Model settings bulk mutation started.', $this->logContext($operation));

        try {
            $this->scope->ensureMutable();

            $normalized = $this->normalizeKeys($keys);

            Log::debug('Model settings bulk mutation prepared.', $this->logContext($operation, [
                'deleted_count' => count($normalized),
            ]));

            $this->repository->deleteMany($this->scope, $normalized);
            $this->model->unsetRelation('modelSettings');

            Log::debug('Model settings bulk mutation completed.', $this->logContext($operation, [
                'deleted_count' => count($normalized),
            ]));
        } catch (Throwable $exception) {
            Log::error('Model settings bulk mutation failed.', $this->logContext($operation, [
                'exception' => $exception::class,
            ]));

            throw $exception;
        }
    }

    public function purge(): void
    {
        $operation = 'purge';

        Log::debug('Model settings bulk mutation started.', $this->logContext($operation));

        try {
            $this->scope->ensureMutable();

            $this->repository->purge($this->scope);
            $this->model->unsetRelation('modelSettings');

            Log::debug('Model settings bulk mutation completed.', $this->logContext($operation));
        } catch (Throwable $exception) {
            Log::error('Model settings bulk mutation failed.', $this->logContext($operation, [
                'exception' => $exception::class,
            ]));

            throw $exception;
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

    protected function logContext(string $operation, array $context = []): array
    {
        return [
            'operation' => $operation,
            'owner'     => $this->model::class,
            'scope'     => $this->scope->isDefault() ? 'default' : 'model',
            ...$context,
        ];
    }
}
