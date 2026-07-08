<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Services;

use DragonCode\LaravelModelSettings\Repositories\SettingsRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionClass;
use UnitEnum;

use function blank;
use function Illuminate\Support\enum_value;
use function property_exists;

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

        $merged = $defaults->replace($model);

        if ($schema = $this->schemaDefaults()) {
            return $schema->replace($merged);
        }

        return $merged;
    }

    public function get(UnitEnum|string|int $key): mixed
    {
        $value = $this->repository->get($this->model, $key);

        if (! blank($value)) {
            return $value;
        }

        $value = $this->repository->get($this->defaultModel(), $key);

        if (! blank($value)) {
            return $value;
        }

        return $this->schemaDefault($key);
    }

    public function set(UnitEnum|string|int $key, mixed $value): void
    {
        blank($value)
            ? $this->repository->delete($this->model, $key)
            : $this->repository->store($this->model, $key, $value);
    }

    public function forget(UnitEnum|string|int $key): void
    {
        $this->repository->delete($this->model, $key);
    }

    /**
     * Return the settings hydrated into the model's typed schema.
     *
     * Values resolve per key: model value, then database default, then the
     * schema's own default.
     *
     * Pass the schema class explicitly to get IDE autocomplete and static
     * analysis on the result. Omit it to hydrate the model's declared schema,
     * which returns `null` when the model declares none.
     *
     * @template TSchema of object
     *
     * @param  class-string<TSchema>|null  $schema
     * @return ($schema is null ? object|null : TSchema)
     */
    public function schema(?string $schema = null): ?object
    {
        $class = $schema ?? $this->schemaClass();

        return $class === null ? null : $this->hydrateSchema($class);
    }

    protected function defaultModel(): Model
    {
        $clone = $this->model->replicateQuietly(['id']);
        $clone->setAttribute($clone->getKeyName(), 0);

        return $clone;
    }

    protected function schemaClass(): ?string
    {
        return $this->model->settingsSchema();
    }

    protected function schemaInstance(): ?object
    {
        $class = $this->schemaClass();

        return $class === null ? null : new $class();
    }

    protected function schemaDefaults(): ?Collection
    {
        $instance = $this->schemaInstance();

        return $instance === null ? null : new Collection(get_object_vars($instance));
    }

    protected function schemaDefault(UnitEnum|string|int $key): mixed
    {
        $instance = $this->schemaInstance();

        if ($instance === null) {
            return null;
        }

        $name = (string) enum_value($key);

        return property_exists($instance, $name) ? $instance->{$name} : null;
    }

    protected function hydrateSchema(string $class): object
    {
        $constructor = (new ReflectionClass($class))->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $arguments[$parameter->getName()] = $this->get($parameter->getName());
        }

        return new $class(...$arguments);
    }
}
