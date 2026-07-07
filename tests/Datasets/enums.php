<?php

declare(strict_types=1);

use Workbench\App\Enums\IntBackedEnum;
use Workbench\App\Enums\StringBackedEnum;
use Workbench\App\Enums\UnitEnum;

dataset('enums', [
    'int enum'    => IntBackedEnum::Foo,
    'string enum' => StringBackedEnum::Bar,
    'unit enum'   => UnitEnum::Baz,
]);
