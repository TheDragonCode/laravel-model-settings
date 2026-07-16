<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\Relation;
use Workbench\App\Casts\CustomCast;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

test('success', function (): void {
    config()->set('model_settings.casts.' . User::class, CustomCast::class);

    $defaultData = [
        'bar' => 'Bar 1',
        'baz' => 'Baz 1',
        'foo' => 'Foo 1',
    ];

    $modelData = [
        'bar' => 'Bar 2',
        'foo' => 'Foo 2',
    ];

    $user = UserFactory::new()->create();

    $user->defaultSettings()->set('foo', $defaultData);
    $user->settings()->set('foo', $modelData);

    $result1 = $user->defaultSettings()->get('foo');
    $result2 = $user->settings()->get('foo');

    expect($result1)->toBe($defaultData);
    expect($result2)->toBe($modelData);
});

test('success with morph map', function (): void {
    $morphMap = Relation::morphMap();

    Relation::morphMap(['user' => User::class], false);

    try {
        config()->set('model_settings.casts.' . User::class, CustomCast::class);

        $data = ['foo' => 'bar'];
        $user = UserFactory::new()->create();

        $user->settings()->set('foo', $data);

        expect($user->settings()->get('foo'))->toBe($data);
        expect($user->modelSettings()->value('item_type'))->toBe('user');
    } finally {
        Relation::morphMap($morphMap, false);
    }
});
