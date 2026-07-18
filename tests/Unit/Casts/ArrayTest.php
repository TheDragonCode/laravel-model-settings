<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Database\Eloquent\Relations\Relation;
use Workbench\App\Casts\ContainerCast;
use Workbench\App\Casts\CustomCast;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

test('model-wide cast remains backward compatible', function (): void {
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

test('exact setting cast resolves through the container for defaults and overrides without affecting other keys', function (): void {
    config()->set('model_settings.casts.' . User::class, [
        'casted' => ContainerCast::class,
    ]);

    $user = UserFactory::new()->create();

    $user->defaultSettings()->set('casted', 'default');
    $user->defaultSettings()->set('plain', ['scope' => 'default']);
    $user->settings()->set('casted', 'override');
    $user->settings()->set('plain', ['scope' => 'override']);

    expect($user->defaultSettings()->get('casted'))->toBe([
        'table' => config()->string('model_settings.table'),
        'value' => 'default',
    ])->and($user->defaultSettings()->get('plain'))->toBe(['scope' => 'default'])
        ->and($user->settings()->get('casted'))->toBe([
            'table' => config()->string('model_settings.table'),
            'value' => 'override',
        ])->and($user->settings()->get('plain'))->toBe(['scope' => 'override']);
});

test('exact setting cast resolves morph aliases to the parent model class', function (): void {
    $morphMap = Relation::morphMap();

    Relation::morphMap(['user' => User::class], false);

    try {
        config()->set('model_settings.casts.' . User::class, [
            'foo' => ContainerCast::class,
        ]);

        $data = ['foo' => 'bar'];
        $user = UserFactory::new()->create();

        $user->settings()->set('foo', $data);

        expect($user->settings()->get('foo'))->toBe([
            'table' => config()->string('model_settings.table'),
            'value' => $data,
        ]);
        expect(Settings::query()->value('item_type'))->toBe('user');
    } finally {
        Relation::morphMap($morphMap, false);
    }
});
