<?php

declare(strict_types=1);

arch()
    ->expect('DragonCode\LaravelModelSettings\Storages')
    ->toBeClasses();

arch()
    ->expect('DragonCode\LaravelModelSettings\Storages')
    ->toHaveSuffix('Storage');

arch()
    ->expect('DragonCode\LaravelModelSettings\Storages')
    ->toBeReadonly();
