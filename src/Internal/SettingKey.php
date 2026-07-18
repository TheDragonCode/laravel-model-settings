<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Internal;

use UnitEnum;

use function Illuminate\Support\enum_value;

final class SettingKey
{
    public static function normalize(int|string|UnitEnum $key): string
    {
        return (string) enum_value($key);
    }
}
