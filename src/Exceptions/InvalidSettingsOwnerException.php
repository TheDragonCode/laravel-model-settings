<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Exceptions;

use DomainException;
use Illuminate\Database\Eloquent\Model;

use function sprintf;

final class InvalidSettingsOwnerException extends DomainException
{
    public static function unsaved(Model $model): self
    {
        return new self(sprintf(
            'Settings cannot be mutated for an unsaved [%s] model.',
            $model::class
        ));
    }

    public static function reservedIdentifier(Model $model): self
    {
        return new self(sprintf(
            'Settings cannot be mutated for [%s] with key [0] because [0] is reserved for class defaults in 1.x.',
            $model::class
        ));
    }
}
