<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\SomeUuid;
use Workbench\Database\Factories\SomeUuidFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

afterEach(function () {
    assertDatabaseHas(Settings::class, ['item_ulid' => null, 'item_id' => null]);
    assertDatabaseMissing(Settings::class, ['item_uuid' => null]);
});

test('default first', function () {
    $user1 = SomeUuidFactory::new()->create();
    $user2 = SomeUuidFactory::new()->create();
    $user3 = SomeUuidFactory::new()->create();

    (new SomeUuid)->defaultSettings()->set('foo', 111);
    (new SomeUuid)->defaultSettings()->set('bar', 222);

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
    $user1 = SomeUuidFactory::new()->create();
    $user2 = SomeUuidFactory::new()->create();
    $user3 = SomeUuidFactory::new()->create();

    $user1->settings()->set('foo', 333);
    $user2->settings()->set('bar', 444);

    (new SomeUuid)->defaultSettings()->set('foo', 111);
    (new SomeUuid)->defaultSettings()->set('bar', 222);

    $result1 = $user1->settings()->all()->sortKeys()->all();
    $result2 = $user2->settings()->all()->sortKeys()->all();
    $result3 = $user3->settings()->all()->sortKeys()->all();

    expect($result1)->toBe(['bar' => 222, 'foo' => 333]);
    expect($result2)->toBe(['bar' => 444, 'foo' => 111]);
    expect($result3)->toBe(['bar' => 222, 'foo' => 111]);
});
