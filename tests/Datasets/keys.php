<?php

declare(strict_types=1);

use Workbench\App\Enums\IntBackedEnum;
use Workbench\App\Enums\StringBackedEnum;
use Workbench\App\Enums\UnitEnum;

dataset('setting keys', [
    'int enum'    => IntBackedEnum::Foo,
    'string enum' => StringBackedEnum::Bar,
    'unit enum'   => UnitEnum::Baz,

    'string' => 'foo',
    'int'    => 10,
]);

dataset('invalid setting keys', [
    'empty string'           => '',
    'space-only string'      => '   ',
    'whitespace-only string' => "\t\r\n",
    'empty backed enum'      => StringBackedEnum::Empty,
    'whitespace backed enum' => StringBackedEnum::Whitespace,
]);
