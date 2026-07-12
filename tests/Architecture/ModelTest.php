<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;

arch()
    ->expect('DragonCode\LaravelModelSettings\Models')
    ->toBeClasses();

arch()
    ->expect('DragonCode\LaravelModelSettings\Models')
    ->toBeFinal();

arch()
    ->expect('DragonCode\LaravelModelSettings\Models')
    ->not->toHaveSuffix('Model');

arch()
    ->expect('DragonCode\LaravelModelSettings\Models')
    ->toExtend(Model::class);
