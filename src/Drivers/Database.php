<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Drivers;

use Illuminate\Contracts\Support\Arrayable;

use function config;

class Database extends Driver
{
    protected function getPayload(): array|Arrayable
    {
        return $this->model->modelSettings?->payload ?? [];
    }

    public function apply(array|Arrayable $settings): static
    {
        if (! $this->model->modelSettings) {
            $class = config('model-settings.repositories.database.model');

            $this->model->setRelation('modelSettings', new $class([
                'item_type' => $this->model->getMorphClass(),
                'item_id'   => $this->model->getKey(),
            ]));
        }

        $this->model->modelSettings->setAttribute('payload', $this->merge($this->getPayload(), $settings));
        $this->model->modelSettings->save();

        if ($this->model->modelSettings->wasRecentlyCreated) {
            $this->model->load('modelSettings');
        }

        $this->cache()->forget();

        return $this;
    }

    public function clear(): static
    {
        $this->model->modelSettings()->delete();
        $this->model->unsetRelation('modelSettings');

        $this->cache()->forget();

        return $this;
    }
}
