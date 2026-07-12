<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

use function in_array;

trait HasIdentifier
{
    use HasModelResolver;

    protected int|string|null $defaultIdentifier = null;

    protected function defaultId(Model $model): int|string
    {
        if ($this->defaultIdentifier) {
            return $this->defaultIdentifier;
        }

        if ($model->getKeyType() === 'int') {
            return $this->defaultIdentifier = 0;
        }

        $traits = (new ReflectionClass($model))->getTraitNames();

        if (in_array(HasUuids::class, $traits, true)) {
            return $this->defaultIdentifier = '019b76da-a800-734b-a5f1-4b5ce56f6062';
        }

        if (in_array(HasUlids::class, $traits, true)) {
            return $this->defaultIdentifier = '01kdvdna0025jjz9zapqg7zgsr';
        }

        return '0';
    }
}
