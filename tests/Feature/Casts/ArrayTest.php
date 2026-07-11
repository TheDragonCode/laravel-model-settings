<?php

declare(strict_types=1);

use Workbench\App\Casts\CustomCast;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

test('success', function () {
    config()->set('model_settings.casts.' . User::class, CustomCast::class);

    $defaultData = [
        'foo' => 'Foo 1',
        'bar' => 'Bar 1',
        'baz' => 'Baz 1',
    ];

    $modelData = [
        'foo' => 'Foo 2',
        'bar' => 'Bar 2',
    ];

    $user = UserFactory::new()->create();

    $user->defaultSettings()->set('foo', $defaultData);
    $user->settings()->set('foo', $modelData);

    $result1 = $user->defaultSettings()->get('foo');
    $result2 = $user->settings()->get('foo');

    expect($result1)->toBe($defaultData);
    expect($result2)->toBe($modelData);
});
