<?php

declare(strict_types=1);

arch()
    ->expect('DragonCode\LaravelModelSettings\Repositories')
    ->not->toHaveSuffix('Model');
