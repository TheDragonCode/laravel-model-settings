<?php

declare(strict_types=1);

use Workbench\App\Models\SomeString;
use Workbench\Database\Factories\SomeStringFactory;

test('default first', function (): void {
    $user1 = SomeStringFactory::new()->create();
    $user2 = SomeStringFactory::new()->create();
    $user3 = SomeStringFactory::new()->create();

    (new SomeString)->defaultSettings()->set('foo', 111);
    (new SomeString)->defaultSettings()->set('bar', 222);

    $user1->settings()->set('foo', 333);
    $user2->settings()->set('bar', 444);

    $result1 = $user1->settings()->all()->sortKeys()->all();
    $result2 = $user2->settings()->all()->sortKeys()->all();
    $result3 = $user3->settings()->all()->sortKeys()->all();

    expect($result1)->toBe(['bar' => 222, 'foo' => 333]);
    expect($result2)->toBe(['bar' => 444, 'foo' => 111]);
    expect($result3)->toBe(['bar' => 222, 'foo' => 111]);
});

test('model first', function (): void {
    $user1 = SomeStringFactory::new()->create();
    $user2 = SomeStringFactory::new()->create();
    $user3 = SomeStringFactory::new()->create();

    $user1->settings()->set('foo', 333);
    $user2->settings()->set('bar', 444);

    (new SomeString)->defaultSettings()->set('foo', 111);
    (new SomeString)->defaultSettings()->set('bar', 222);

    $result1 = $user1->settings()->all()->sortKeys()->all();
    $result2 = $user2->settings()->all()->sortKeys()->all();
    $result3 = $user3->settings()->all()->sortKeys()->all();

    expect($result1)->toBe(['bar' => 222, 'foo' => 333]);
    expect($result2)->toBe(['bar' => 444, 'foo' => 111]);
    expect($result3)->toBe(['bar' => 222, 'foo' => 111]);
});
