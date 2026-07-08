<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Services;

use DragonCode\LaravelModelSettings\Repositories\SettingsRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LogicException;
use ReflectionClass;
use ReflectionParameter;
use UnitEnum;

use function blank;
use function Illuminate\Support\enum_value;
use function method_exists;
use function property_exists;
use function sprintf;

/**
 * @template TSchema of object
 *
 * @mixin TSchema
 */
class SettingsService
{
    protected ?Model $defaultModel = null;

    protected ?Collection $schemaDefaults = null;

    public function __construct(
        protected Model $model,
        protected SettingsRepository $repository,
    ) {}

    /**
     * Read a setting as a property: `$user->settings()->timezone`.
     */
    public function __get(string $name): mixed
    {
        $this->ensurePropertyKey($name);

        return $this->get($name);
    }

    /**
     * Write a setting as a property: `$user->settings()->timezone = 'UTC'`.
     *
     * Assigning a blank value (`null`, `''`, `[]`) removes the model setting,
     * mirroring `set()`, so the value falls back to its default.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->ensurePropertyKey($name);

        $this->set($name, $value);
    }

    public function __isset(string $name): bool
    {
        $this->ensurePropertyKey($name);

        return $this->get($name) !== null;
    }

    public function __unset(string $name): void
    {
        $this->ensurePropertyKey($name);

        $this->forget($name);
    }

    public function all(): Collection
    {
        return $this->schemaDefaults()->replace($this->storedValues());
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

        return $this->schemaDefaults()->get((string) enum_value($key));
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
     * Stored values (model values, then database defaults) are passed to the
     * schema constructor; keys without a stored value are omitted, so the
     * constructor defaults of the hydrated class apply.
     *
     * Pass the schema class explicitly to get IDE autocomplete and static
     * analysis on the result. Omit it to hydrate the model's declared schema,
     * which returns `null` when the model declares none.
     *
     * @template TExplicit of object
     *
     * @param  class-string<TExplicit>|null  $schema
     * @return ($schema is null ? TSchema|null : TExplicit)
     */
    public function schema(?string $schema = null): ?object
    {
        $class = $schema ?? $this->schemaClass();

        return $class === null ? null : $this->hydrateSchema($class);
    }

    protected function defaultModel(): Model
    {
        if ($this->defaultModel !== null) {
            return $this->defaultModel;
        }

        $clone = $this->model->replicateQuietly(['id']);
        $clone->setAttribute($clone->getKeyName(), 0);

        return $this->defaultModel = $clone;
    }

    /**
     * Database defaults merged with model values. Model values win.
     */
    protected function storedValues(): Collection
    {
        return $this->repository->all($this->defaultModel())
            ->replace($this->repository->all($this->model));
    }

    protected function schemaClass(): ?string
    {
        return method_exists($this->model, 'settingsSchema')
            ? $this->model->settingsSchema()
            : null;
    }

    protected function schemaDefaults(): Collection
    {
        return $this->schemaDefaults ??= $this->constructorDefaults($this->schemaClass());
    }

    /**
     * Collect the schema defaults from the promoted constructor parameters
     * without instantiating the class, so a schema with a required parameter
     * cannot break reads.
     */
    protected function constructorDefaults(?string $class): Collection
    {
        $defaults = new Collection;

        if ($class === null) {
            return $defaults;
        }

        foreach ($this->constructorParameters($class) as $parameter) {
            if ($parameter->isPromoted() && $parameter->isDefaultValueAvailable()) {
                $defaults->put($parameter->getName(), $parameter->getDefaultValue());
            }
        }

        return $defaults;
    }

    /** @return array<ReflectionParameter> */
    protected function constructorParameters(string $class): array
    {
        return (new ReflectionClass($class))->getConstructor()?->getParameters() ?? [];
    }

    /**
     * Blank stored values are skipped, mirroring `get()`, so the constructor
     * default of the hydrated class applies to them.
     */
    protected function hydrateSchema(string $class): object
    {
        $stored = $this->storedValues()->reject(fn (mixed $value): bool => blank($value));

        $arguments = [];

        foreach ($this->constructorParameters($class) as $parameter) {
            if ($stored->has($parameter->getName())) {
                $arguments[$parameter->getName()] = $stored->get($parameter->getName());
            }
        }

        return new $class(...$arguments);
    }

    /**
     * Property access is routed to settings only for names that cannot collide
     * with the service's own properties; colliding names fail loudly instead
     * of silently reading or writing an unexpected setting.
     */
    protected function ensurePropertyKey(string $name): void
    {
        if (property_exists($this, $name)) {
            throw new LogicException(sprintf(
                'The setting [%s] cannot be accessed as a property: the name collides with an internal %s property. Use get() / set() instead.',
                $name,
                static::class,
            ));
        }
    }
}
