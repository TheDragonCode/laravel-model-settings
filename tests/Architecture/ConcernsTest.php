<?php

declare(strict_types=1);

arch()
    ->expect('DragonCode\LaravelModelSettings\Concerns')
    ->toBeTraits();

arch()
    ->expect('DragonCode\LaravelModelSettings\Concerns')
    ->toHavePrefix('Has');

arch()
    ->expect('DragonCode\LaravelModelSettings\Concerns')
    ->not->toHaveSuffix('Concern');
