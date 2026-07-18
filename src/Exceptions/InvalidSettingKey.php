<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Exceptions;

use DomainException;

final class InvalidSettingKey extends DomainException
{
    public static function blank(): self
    {
        return new self('Setting keys cannot be empty or contain only whitespace.');
    }
}
