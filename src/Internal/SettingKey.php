<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Internal;

use DragonCode\LaravelModelSettings\Exceptions\InvalidSettingKey;
use UnitEnum;

use function Illuminate\Support\enum_value;
use function trim;

final class SettingKey
{
    public static function normalize(int|string|UnitEnum $key): string
    {
        $normalized = (string) enum_value($key);

        if (trim($normalized) === '') {
            throw InvalidSettingKey::blank();
        }

        return $normalized;
    }
}
