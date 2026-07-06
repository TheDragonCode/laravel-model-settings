<?php

declare(strict_types=1);

namespace Workbench\App\Enums;

enum IntBackedEnum: int
{
    case Foo = 11;
    case Bar = 22;
}
