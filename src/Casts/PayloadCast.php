<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Casts;

use DragonCode\LaravelModelSettings\Internal\PayloadCastResolver;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use Spatie\LaravelData\Data;

use function app;

class PayloadCast implements CastsAttributes
{
    protected int $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $cast = $this->resolver()->resolve($model, $this->settingKey($model, $attributes));

        if ($cast === null) {
            return $this->fromJson($value);
        }

        if ($cast instanceof CastsAttributes) {
            return $cast->get($model, $key, $value, $attributes);
        }

        return $cast::from($value);
    }

    /** @throws JsonException */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        $cast = $this->resolver()->resolve($model, $this->settingKey($model, $attributes));

        if ($cast === null) {
            return $this->asJson($value);
        }

        if (is_string($cast) && $value instanceof Data) {
            return $value->toJson($this->flags);
        }

        if ($cast instanceof CastsAttributes) {
            $value = $cast->set($model, $key, $value, $attributes);
        }

        return $this->asJson($value);
    }

    protected function fromJson($value): array|bool|float|int|string|null
    {
        return Json::decode($value);
    }

    protected function asJson($value): string
    {
        return Json::encode($value, $this->flags);
    }

    protected function settingKey(Model $model, array $attributes): string
    {
        return (string) ($attributes['key'] ?? $model->getAttribute('key'));
    }

    protected function resolver(): PayloadCastResolver
    {
        return app(PayloadCastResolver::class);
    }
}
