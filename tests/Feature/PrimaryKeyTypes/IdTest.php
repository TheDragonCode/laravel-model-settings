<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\SomeId;
use Workbench\Database\Factories\SomeIdFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

afterEach(function () {
    assertDatabaseHas(Settings::class, ['item_uuid' => null, 'item_ulid' => null]);
    assertDatabaseMissing(Settings::class, ['item_id' => null]);
});

test('default first', function () {
    $user1 = SomeIdFactory::new()->create();
    $user2 = SomeIdFactory::new()->create();
    $user3 = SomeIdFactory::new()->create();

    (new SomeId)->defaultSettings()->set('foo', 111);
    (new SomeId)->defaultSettings()->set('bar', 222);

    $user1->settings()->set('foo', 333);
    $user2->settings()->set('bar', 444);

    $result1 = $user1->settings()->all()->sortKeys()->all();
    $result2 = $user2->settings()->all()->sortKeys()->all();
    $result3 = $user3->settings()->all()->sortKeys()->all();

    expect($result1)->toBe(['bar' => 222, 'foo' => 333]);
    expect($result2)->toBe(['bar' => 444, 'foo' => 111]);
    expect($result3)->toBe(['bar' => 222, 'foo' => 111]);
});

test('model first', function () {
    $user1 = SomeIdFactory::new()->create();
    $user2 = SomeIdFactory::new()->create();
    $user3 = SomeIdFactory::new()->create();

    $user1->settings()->set('foo', 333);
    $user2->settings()->set('bar', 444);

    (new SomeId)->defaultSettings()->set('foo', 111);
    (new SomeId)->defaultSettings()->set('bar', 222);

    $result1 = $user1->settings()->all()->sortKeys()->all();
    $result2 = $user2->settings()->all()->sortKeys()->all();
    $result3 = $user3->settings()->all()->sortKeys()->all();

    expect($result1)->toBe(['bar' => 222, 'foo' => 333]);
    expect($result2)->toBe(['bar' => 444, 'foo' => 111]);
    expect($result3)->toBe(['bar' => 222, 'foo' => 111]);
});
