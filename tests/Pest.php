<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

pest()
    ->uses(TestCase::class)
    ->in('Architecture');

pest()
    ->uses(TestCase::class, WithWorkbench::class, RefreshDatabase::class)
    ->in('Unit')
    ->afterEach(function () {
        Carbon::setTestNow('2026-07-06T20:51:24+00:00');
    });
