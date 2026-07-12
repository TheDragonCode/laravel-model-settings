<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('success', function (): void {
    Model::automaticallyEagerLoadRelationships();

    $recorder = new QueryRecorder;

    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();
    $user3 = UserFactory::new()->create();

    (new User)->defaultSettings()->set('qwerty', 111);

    $user1->settings()->set('foo', 222);
    $user2->settings()->set('bar', 333);
    $user3->settings()->set('baz', 444);

    $recorder->start();

    User::query()->each(
        fn (User $user) => $user->settings()->get('foo')
    );

    expect($recorder->calls())->toBe(2);
});
