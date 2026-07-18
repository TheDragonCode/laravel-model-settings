<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Exceptions;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Throwable;

use function sprintf;

final class BulkMutationException extends RuntimeException
{
    public static function setMany(Model $owner, bool $defaultScope, Throwable $previous): self
    {
        return new self(sprintf(
            'Model settings [setMany] failed for [%s] in [%s] scope.',
            $owner::class,
            $defaultScope ? 'default' : 'model'
        ), previous: $previous);
    }

    public static function forgetMany(Model $owner, bool $defaultScope, Throwable $previous): self
    {
        return new self(sprintf(
            'Model settings [forgetMany] failed for [%s] in [%s] scope.',
            $owner::class,
            $defaultScope ? 'default' : 'model'
        ), previous: $previous);
    }

    public static function purge(Model $owner, bool $defaultScope, Throwable $previous): self
    {
        return new self(sprintf(
            'Model settings [purge] failed for [%s] in [%s] scope.',
            $owner::class,
            $defaultScope ? 'default' : 'model'
        ), previous: $previous);
    }
}
