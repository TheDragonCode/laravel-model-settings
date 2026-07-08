<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

pest()
    ->uses(TestCase::class)
    ->in('Architecture');

pest()
    ->uses(TestCase::class, WithWorkbench::class, RefreshDatabase::class)
    ->in('Feature', 'Unit');
