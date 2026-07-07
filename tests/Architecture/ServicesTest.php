<?php

declare(strict_types=1);

arch()
    ->expect('DragonCode\LaravelModelSettings\Services')
    ->toBeClasses();

arch()
    ->expect('DragonCode\LaravelModelSettings\Services')
    ->toHaveSuffix('Service');

arch()
    ->expect('DragonCode\LaravelModelSettings\Services')
    ->not->toBeReadonly();

arch()
    ->expect('DragonCode\LaravelModelSettings\Services')
    ->not->toBeFinal();
