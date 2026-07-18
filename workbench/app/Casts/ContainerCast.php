<?php

declare(strict_types=1);

namespace Workbench\App\Casts;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;

class ContainerCast implements CastsAttributes
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return Json::decode($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        return [
            'table' => $this->config->get('model_settings.table'),
            'value' => $value,
        ];
    }
}
