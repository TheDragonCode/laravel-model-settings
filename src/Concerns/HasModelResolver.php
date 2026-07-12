<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

use function config;

trait HasModelResolver
{
    protected ?string $parentModelClass = null;

    protected ?string $parentModelColumn = null;

    protected ?Model $settingsModel = null;

    protected function parentModelClass(Model $model): string
    {
        if ($this->parentModelClass) {
            return $this->parentModelClass;
        }

        $type = $this->modelTypeColumn($model);

        return $this->parentModelClass = Relation::getMorphedModel($type) ?? $type;
    }

    protected function modelIdColumn(Model $model): string
    {
        if ($this->parentModelColumn) {
            return $this->parentModelColumn;
        }

        /** @var class-string<Model> $class */
        $class = $this->parentModelClass($model);

        $column = (new $class)->getKeyName();

        return $this->parentModelColumn = 'item_' . $column;
    }

    protected function modelTypeColumn(Model $model): string
    {
        if ($model instanceof Settings) {
            return $model->getAttribute('item_type');
        }

        return $model::class;
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
