<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Exceptions;

use DomainException;
use Throwable;

use function sprintf;

final class InvalidPayloadCast extends DomainException
{
    public static function invalidType(string $parent, string $key): self
    {
        return new self(sprintf(
            'Payload cast configured for [%s] setting [%s] must be a class-string.',
            $parent,
            $key
        ));
    }

    public static function missing(string $parent, string $key, string $cast): self
    {
        return new self(sprintf(
            'Payload cast [%s] configured for [%s] setting [%s] does not exist.',
            $cast,
            $parent,
            $key
        ));
    }

    public static function unsupported(string $parent, string $key, string $cast): self
    {
        return new self(sprintf(
            'Payload cast [%s] configured for [%s] setting [%s] must implement CastsAttributes or extend Data.',
            $cast,
            $parent,
            $key
        ));
    }

    public static function unresolvable(string $parent, string $key, string $cast, Throwable $previous): self
    {
        return new self(sprintf(
            'Payload cast [%s] configured for [%s] setting [%s] could not be resolved by the container.',
            $cast,
            $parent,
            $key
        ), previous: $previous);
    }
}
