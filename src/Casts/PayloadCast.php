<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\LaravelData\Data;

use function blank;
use function class_exists;
use function config;
use function json_decode;
use function json_encode;
use function json_validate;

class PayloadCast implements CastsAttributes
{
    protected int $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (blank($value)) {
            return null;
        }

        if (! $cast = $this->cast($model)) {
            return $this->decode($value);
        }

        if (class_exists(Data::class) && $cast instanceof Data) {
            return $cast::from($this->decode($value));
        }

        return new $cast(...$this->decode($value));
    }

    /**
     * @throws \JsonException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return match (true) {
            $value instanceof Jsonable  => $value->toJson($this->flags),
            $value instanceof Arrayable => $this->encode($value->toArray()),
            default                     => $this->encode($value),
        };
    }

    protected function cast(Model $model): ?string
    {
        if (! $parent = $this->parentModel($model)) {
            return null;
        }

        return $this->casts()[$parent] ?? null;
    }

    protected function parentModel(Model $model): ?string
    {
        return Relation::getMorphedModel($model->item_type) ?? $model->item_type;
    }

    protected function casts(): array
    {
        return config()->array('model_settings.casts', []);
    }

    /**
     * @throws \JsonException
     */
    protected function encode(mixed $value): string
    {
        return json_encode($value, $this->flags);
    }

    protected function decode(string $value): mixed
    {
        if (! json_validate($value)) {
            return null;
        }

        return json_decode($value, true);
    }
}
