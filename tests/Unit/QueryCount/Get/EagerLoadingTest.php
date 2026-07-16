<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('success', function (): void {
    $recorder = new QueryRecorder;

    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();
    $user3 = UserFactory::new()->create();

    (new User)->defaultSettings()->set('foo', 222);

    $user1->settings()->set('foo', 333);

    $recorder->start();

    $users = User::query()
        ->with('modelSettings')
        ->get()
        ->keyBy('id');

    $result1 = $users[$user1->id]->settings()->get('foo');
    $result2 = $users[$user2->id]->settings()->get('foo');
    $result3 = $users[$user3->id]->settings()->get('bar');

    expect($result1)->toBe(333);
    expect($result2)->toBe(222);
    expect($result3)->toBeNull();

    expect($recorder->calls())->toBe(2);
});
