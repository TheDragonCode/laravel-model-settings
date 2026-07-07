<?php

declare(strict_types=1);

arch()
    ->expect('DragonCode\LaravelModelSettings\Models')
    ->toBeClasses();

arch()
    ->expect('DragonCode\LaravelModelSettings\Models')
    ->not->toHaveSuffix('Model');
