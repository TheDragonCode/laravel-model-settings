<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('has distinguishes stored null values from missing keys', function (): void {
    $user     = UserFactory::new()->create();
    $defaults = (new User)->defaultSettings();

    $defaults->set('default-null', null);
    $defaults->set('shadowed', 'default');
    $user->settings()->set('shadowed', null);

    expect($defaults->has('default-null'))->toBeTrue()
        ->and($defaults->get('default-null'))->toBeNull()
        ->and($defaults->has('missing'))->toBeFalse()
        ->and($user->settings()->has('default-null'))->toBeTrue()
        ->and($user->settings()->get('default-null'))->toBeNull()
        ->and($user->settings()->has('shadowed'))->toBeTrue()
        ->and($user->settings()->get('shadowed'))->toBeNull()
        ->and($user->settings()->has('missing'))->toBeFalse();
});

test('has reuses an eager-loaded relation without another query', function (): void {
    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->set('default-null', null);
    $user->settings()->set('override-null', null);

    $eager = User::query()->with('modelSettings')->findOrFail($user->getKey());

    $recorder = new QueryRecorder;
    $recorder->start();

    expect($eager->settings()->has('default-null'))->toBeTrue()
        ->and($eager->settings()->has('override-null'))->toBeTrue()
        ->and($eager->settings()->has('missing'))->toBeFalse()
        ->and($recorder->calls())->toBe(0);
});
