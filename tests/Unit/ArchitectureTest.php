<?php

declare(strict_types=1);

arch()
    ->expect('DragonCode\LaravelModelSettings')
    ->not->toUse([
        'dd',
        'die',
        'dump',
        'echo',
        'exit',
        'print_r',
        'printf',
        'ray',
        'var_dump',
    ]);

arch()
    ->expect('DragonCode\LaravelModelSettings')
    ->toUseStrictTypes();
