<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\LaravelData\Data;

use function blank;
use function class_exists;
use function config;
use function is_a;

class PayloadCast implements CastsAttributes
{
    protected int $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (blank($value)) {
            return null;
        }

        if (! $cast = $this->cast($model)) {
            return $this->fromJson($value);
        }

        if (is_a($cast, CastsAttributes::class, true)) {
            return (new $cast())->get($model, $key, $value, $attributes);
        }

        if (class_exists(Data::class) && is_a($cast, Data::class, true)) {
            return $cast::from($value);
        }

        return $this->fromJson($value);
    }

    /**
     * @throws \JsonException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (! $cast = $this->cast($model)) {
            return $this->asJson($value);
        }

        if (class_exists(Data::class) && $value instanceof Data) {
            return $value->toJson($this->flags);
        }

        if (is_a($cast, CastsAttributes::class, true)) {
            $value = (new $cast())->set($model, $key, $value, $attributes);
        }

        return $this->asJson($value);
    }

    protected function fromJson($value): array|string|int|float|bool|null
    {
        return Json::decode($value);
    }

    protected function asJson($value): string
    {
        return Json::encode($value, $this->flags);
    }

    protected function cast(Model $model): ?string
    {
        $parent = $this->parentModel($model);

        return $this->casts()[$parent] ?? null;
    }

    protected function parentModel(Model $model): string
    {
        $type = $model->getAttribute('item_type');

        return Relation::getMorphedModel($type) ?? $type;
    }

    protected function casts(): array
    {
        return config()->array('model_settings.casts', []);
    }
}
