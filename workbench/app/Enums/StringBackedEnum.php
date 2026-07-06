<?php

declare(strict_types=1);

namespace Workbench\App\Enums;

enum StringBackedEnum: string
{
    case Foo = 'foo';
    case Bar = 'bar';
}
