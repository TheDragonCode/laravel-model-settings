<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

pest()
    ->uses(TestCase::class, WithWorkbench::class, RefreshDatabase::class)
    ->in('Unit')
    ->afterEach(function () {
        expect('fallback')->toMatchSnapshot();
    });
