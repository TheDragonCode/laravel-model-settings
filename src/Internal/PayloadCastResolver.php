<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Internal;

use DragonCode\LaravelModelSettings\Concerns\HasModelResolver;
use DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;
use Throwable;

use function array_key_exists;
use function class_exists;
use function config;
use function is_a;
use function is_array;
use function is_string;

final class PayloadCastResolver
{
    use HasModelResolver;

    public function __construct(
        protected Container $container,
    ) {}

    public function resolve(Model $model, string $key): CastsAttributes|string|null
    {
        $parent = $this->parentModelClass($model);

        [$configured, $cast] = $this->configuredCast($parent, $key);

        if (! $configured) {
            return null;
        }

        if (! is_string($cast)) {
            throw InvalidPayloadCast::invalidType($parent, $key);
        }

        if (! class_exists($cast)) {
            throw InvalidPayloadCast::missing($parent, $key, $cast);
        }

        if (is_a($cast, CastsAttributes::class, true)) {
            return $this->resolveEloquentCast($parent, $key, $cast);
        }

        if (class_exists(Data::class) && is_a($cast, Data::class, true)) {
            return $cast;
        }

        throw InvalidPayloadCast::unsupported($parent, $key, $cast);
    }

    protected function configuredCast(string $parent, string $key): array
    {
        $casts = config()->array('model_settings.casts', []);

        if (! array_key_exists($parent, $casts)) {
            return [false, null];
        }

        $configured = $casts[$parent];

        if (! is_array($configured)) {
            return [true, $configured];
        }

        if (! array_key_exists($key, $configured)) {
            return [false, null];
        }

        return [true, $configured[$key]];
    }

    protected function resolveEloquentCast(string $parent, string $key, string $cast): CastsAttributes
    {
        try {
            $resolved = $this->container->make($cast);
        } catch (Throwable $exception) {
            throw InvalidPayloadCast::unresolvable($parent, $key, $cast, $exception);
        }

        if (! $resolved instanceof CastsAttributes) {
            throw InvalidPayloadCast::unsupported($parent, $key, $cast);
        }

        return $resolved;
    }
}
