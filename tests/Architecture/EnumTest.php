<?php

declare(strict_types=1);

arch()
    ->expect('DragonCode\LaravelModelSettings\Enums')
    ->toBeEnum();

arch()
    ->expect('DragonCode\LaravelModelSettings\Enums')
    ->toHaveSuffix('Enum');
