<?php

use DragonCode\LaravelModelSettings\Tests\TestCase;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Storage;

pest()
    ->uses(TestCase::class)
    ->in('Unit')
    ->afterEach(function () {
        expect(['ends of snapshot'])->toMatchSnapshot();
    });

pest()
    ->in('Unit/Database')
    ->beforeEach(function () {
        config()->set('model-settings.default', 'database');
    });

pest()
    ->in('Unit/Redis')
    ->beforeEach(function () {
        config()->set('model-settings.default', 'redis');
    });

pest()
    ->in('Unit/File')
    ->beforeEach(function () {
        config()->set('model-settings.default', 'file');
        config()->set('model-settings.repositories.file.directory', 'settings/' . (int) ParallelTesting::token());

        $storage = Storage::disk(config('model-settings.repositories.file.disk'));
        $path    = config('model-settings.repositories.file.directory');

        if ($storage->exists($path)) {
            $storage->deleteDirectory($path);
        }
    });
