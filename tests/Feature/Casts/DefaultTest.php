<?php

declare(strict_types=1);

use Workbench\Database\Factories\UserFactory;

test('success', function (mixed $default, mixed $model) {
    $user = UserFactory::new()->create();

    $user->defaultSettings()->set('foo', $default);
    $user->settings()->set('foo', $model);

    $result1 = $user->defaultSettings()->get('foo');
    $result2 = $user->settings()->get('foo');

    expect($result1)->toBe($default);
    expect($result2)->toBe($model);
})->with([
    'number' => ['default' => 111, 'model' => 222],
    'string' => ['default' => 'qwe', 'model' => 'rty'],
    'array'  => [
        'default' => ['q1' => 'w1', 'q2' => 'w2'],
        'model'   => ['q3' => 'w3', 'q4' => 'w4'],
    ],
]);
