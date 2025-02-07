<?php

declare(strict_types=1);

namespace Workbench\App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SettingsData extends Data
{
    public Optional|string $foo;

    public Optional|BarData $bar;

    public Optional|QweData $qwe;
}
