<?php

declare(strict_types=1);

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

arch()
    ->expect('DragonCode\LaravelModelSettings\Casts')
    ->toBeClasses();

arch()
    ->expect('DragonCode\LaravelModelSettings\Casts')
    ->not->toBeFinal();

arch()
    ->expect('DragonCode\LaravelModelSettings\Casts')
    ->toHaveSuffix('Cast');

arch()
    ->expect('DragonCode\LaravelModelSettings\Casts')
    ->toImplement(CastsAttributes::class);
