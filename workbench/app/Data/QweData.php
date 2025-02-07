<?php

declare(strict_types=1);

namespace Workbench\App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class QweData extends Data
{
    public Optional|string $rty;
}
