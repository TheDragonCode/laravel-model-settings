<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('unsaved model does not read settings', function (bool $hasKey): void {
    (new User)->defaultSettings()->set('foo', 111);

    $user = new User;

    if ($hasKey) {
        $user->setAttribute($user->getKeyName(), 123);
    }

    $recorder = new QueryRecorder;
    $recorder->start();

    expect($user->settings()->get('foo'))->toBeNull();
    expect($user->settings()->all())->toBeEmpty();
    expect($user->relationLoaded('modelSettings'))->toBeFalse();
    expect($recorder->calls())->toBe(0);
})->with([
    'missing key'     => false,
    'preassigned key' => true,
]);

test('replicated model ignores copied settings relation', function (): void {
    (new User)->defaultSettings()->set('foo', 111);

    $user = UserFactory::new()->create();
    $user->settings()->set('foo', 222);
    $user->load('modelSettings');

    $replica = $user->replicate();

    $recorder = new QueryRecorder;
    $recorder->start();

    expect($replica->relationLoaded('modelSettings'))->toBeTrue();
    expect($replica->settings()->get('foo'))->toBeNull();
    expect($replica->settings()->all())->toBeEmpty();
    expect($recorder->calls())->toBe(0);
});
