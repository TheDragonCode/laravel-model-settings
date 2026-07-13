<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

test('eager loading includes default and model settings for each owner', function (): void {
    [$user1, $user2, $user3] = UserFactory::new()->count(3)->create();

    (new User)->defaultSettings()->set('foo', 111);
    (new User)->defaultSettings()->set('bar', 222);

    $user1->settings()->set('foo', 333);
    $user2->settings()->set('bar', 444);

    $users = User::query()
        ->with('modelSettings')
        ->get();

    expect($users[0]->settings()->all()->sortKeys()->all())->toBe(['bar' => 222, 'foo' => 333]);
    expect($users[1]->settings()->all()->sortKeys()->all())->toBe(['bar' => 444, 'foo' => 111]);
    expect($users[2]->settings()->all()->sortKeys()->all())->toBe(['bar' => 222, 'foo' => 111]);
});
