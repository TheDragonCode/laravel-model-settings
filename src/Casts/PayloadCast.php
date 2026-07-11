<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Spatie\LaravelData\Data;

use function blank;
use function class_exists;
use function config;
use function is_a;
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

        if (class_exists(Data::class) && is_a($cast, Data::class, true)) {
            return $cast::from($value);
        }

        if (class_exists($cast)) {
            return new $cast(...$this->decode($value));
        }

        return match ($cast) {
            'int',
            'integer'                   => (int) $value,
            'string'                    => (string) $value,
            'bool',
            'boolean'                   => (bool) $value,
            'object'                    => $this->fromJson($value, true),
            'collection'                => new Collection($this->fromJson($value)),
            'date'                      => $this->fromDate($value),
            'datetime',
            'custom_datetime'           => $this->fromDateTime($value),
            'immutable_date'            => $this->fromDate($value)->toImmutable(),
            'immutable_datetime',
            'immutable_custom_datetime' => $this->fromDateTime($value)->toImmutable(),
            'timestamp'                 => $this->fromTimestamp($value),
            default                     => $this->fromJson($value)
        };
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

    protected function fromJson($value, $asObject = false): array|int|string|float|bool|null
    {
        return Json::decode($value, ! $asObject);
    }

    protected function fromDate(string $value): ?Carbon
    {
        return Carbon::parse($value)->startOfDay();
    }

    protected function fromDateTime(string $value): ?Carbon
    {
        return Date::parse($value);
    }

    protected function fromTimestamp(string $value): int
    {
        return $this->fromDateTime($value)->timestamp;
    }

    protected function cast(Model $model): ?string
    {
        $parent = $this->parentModel($model);

        return $this->casts()[$parent] ?? null;
    }

    protected function parentModel(Model $model): string
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
