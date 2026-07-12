<?php

declare(strict_types=1);

arch()
    ->expect('DragonCode\LaravelModelSettings\Scopes')
    ->toBeClasses();

arch()
    ->expect('DragonCode\LaravelModelSettings\Scopes')
    ->not->toBeFinal();

arch()
    ->expect('DragonCode\LaravelModelSettings\Scopes')
    ->toHaveSuffix('Scope');

arch()
    ->expect('DragonCode\LaravelModelSettings\Scopes')
    ->toBeInvokable();
