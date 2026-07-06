<?php

declare(strict_types=1);

arch()
    ->expect('DragonCode\LaravelModelSettings\Repositories')
    ->toBeClasses();

arch()
    ->expect('DragonCode\LaravelModelSettings\Repositories')
    ->toHaveSuffix('Repository');

arch()
    ->expect('DragonCode\LaravelModelSettings\Repositories')
    ->toBeReadonly();
