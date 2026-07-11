<?php

declare(strict_types=1);

use Workbench\App\Data\SomeData;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

test('success', function () {
    config()->set('model_settings.casts.' . User::class, SomeData::class);

    $defaultData = [
        'foo' => 'Foo 1',
        'bar' => 'Bar 1',
        'baz' => 'Baz 1',

        'item' => [
            'firstName' => 'John 1',
            'lastName'  => 'Doe 1',
        ],

        'collection' => [
            ['id' => 11, 'comment' => 'Comment 11'],
            ['id' => 12, 'comment' => 'Comment 12'],
        ],
    ];

    $modelData = [
        'foo' => 'Foo 2',
        'bar' => 'Bar 2',

        'item' => [
            'firstName' => 'John 2',
            'lastName'  => 'Doe 2',
        ],

        'collection' => [
            ['id' => 21, 'comment' => 'Comment 21'],
            ['id' => 22, 'comment' => 'Comment 22'],
        ],
    ];

    $user = UserFactory::new()->create();

    $user->defaultSettings()->set('foo', $defaultData);
    $user->settings()->set('foo', $modelData);

    $result1 = $user->defaultSettings()->get('foo');
    $result2 = $user->settings()->get('foo');

    expect($result1->toArray())->toBe($defaultData);
    expect($result2->toArray())->toBe($modelData);
});
