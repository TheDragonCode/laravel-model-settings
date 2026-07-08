<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

test('success', function () {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();
    $user3 = UserFactory::new()->create();

    (new User)->defaultSettings()->set('foo', 111);
    (new User)->defaultSettings()->set('bar', 222);

    $user1->settings()->set('foo', 333);
    $user2->settings()->set('bar', 444);

    $result1foo = $user1->settings()->get('foo');
    $result1bar = $user1->settings()->get('bar');

    $result2foo = $user2->settings()->get('foo');
    $result2bar = $user2->settings()->get('bar');

    $result3foo = $user3->settings()->get('foo');
    $result3bar = $user3->settings()->get('bar');

    expect($result1foo)->toBe(333);
    expect($result1bar)->toBe(222);

    expect($result2foo)->toBe(111);
    expect($result2bar)->toBe(444);

    expect($result3foo)->toBe(111);
    expect($result3bar)->toBe(222);
});
