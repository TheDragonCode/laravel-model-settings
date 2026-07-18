<?php

declare(strict_types=1);

namespace Workbench\App\Enums;

enum StringBackedEnum: string
{
    case Bar        = 'bar';
    case Empty      = '';
    case Whitespace = '   ';
}
