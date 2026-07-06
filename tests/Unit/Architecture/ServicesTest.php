<?php

declare(strict_types=1);

arch()
    ->expect('DragonCode\LaravelModelSettings\Services')
    ->toBeClasses();

arch()
    ->expect('DragonCode\LaravelModelSettings\Services')
    ->toHaveSuffix('Service');
