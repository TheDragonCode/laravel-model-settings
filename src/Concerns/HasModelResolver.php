<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

use function config;

trait HasModelResolver
{
    protected ?string $parentModelClass = null;

    protected ?Model $settingsModel = null;

    protected function parentModelClass(Model $model): string
    {
        if ($this->parentModelClass) {
            return $this->parentModelClass;
        }

        $type = $model->getAttribute('item_type');

        return $this->parentModelClass = Relation::getMorphedModel($type) ?? $type;
    }

    protected function settingsModel(): Model
    {
        if ($this->settingsModel) {
            return $this->settingsModel;
        }

        $model = config()->string('model_settings.model');

        return $this->settingsModel = new $model;
    }
}
