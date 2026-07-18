<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;

arch()
    ->expect('DragonCode\LaravelModelSettings')
    ->toUseStrictTypes();

arch()
    ->expect('DragonCode\LaravelModelSettings')
    ->toUseStrictEquality();

arch('library code does not depend on application logging')
    ->expect('DragonCode\LaravelModelSettings')
    ->not->toUse(Log::class);
