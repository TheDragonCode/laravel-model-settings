<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Drivers;

use DragonCode\Cache\Services\Cache as DragonCache;
use DragonCode\LaravelModelSettings\Services\Cache;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use function blank;
use function config;
use function data_forget;
use function data_get;
use function data_set;

abstract class Driver
{
    /** @var Model|\DragonCode\LaravelModelSettings\Concerns\HasSettings */
    protected Model $model;

    abstract public function apply(array|Arrayable $settings): static;

    abstract public function clear(): static;

    abstract protected function getPayload(): array|Arrayable;

    public function __construct(
        protected Cache $cache
    ) {}

    public function forModel(Model $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function all(): array|Arrayable
    {
        return $this->cache()->remember(
            fn () => $this->merge($this->getDefaultSettings(), $this->getPayload())
        );
    }

    public function has(string $path): bool
    {
        return $this->get($path) !== null;
    }

    public function get(string $path, mixed $default = null): mixed
    {
        return data_get($this->getPayload(), $path, $default);
    }

    public function set(string $path, mixed $value): static
    {
        $settings = $this->all();

        data_set($settings, $path, $value);

        return $this->apply($settings);
    }

    public function forget(string $path): static
    {
        $settings = $this->all();

        data_forget($settings, $path);

        return $this->apply($settings);
    }

    protected function merge(array|Arrayable $payload, array|Arrayable $settings): array
    {
        $payload  = $this->dotted($payload);
        $settings = $this->dotted($settings);

        return $payload->merge($settings)->reject(
            static fn (mixed $value) => blank($value)
        )->undot()->all();
    }

    protected function dotted(array|Arrayable $payload): Collection
    {
        $collection = (new Collection($payload))->toArray();

        return (new Collection($collection))->dot();
    }

    protected function getDefaultSettings(): array
    {
        return config('model-settings.settings.' . $this->model->getTable(), []);
    }

    protected function cache(): DragonCache
    {
        return (clone $this->cache)->key(
            config('model-settings.cache.prefix'),
            $this->model->getTable(),
            $this->model->getKey(),
        );
    }
}
