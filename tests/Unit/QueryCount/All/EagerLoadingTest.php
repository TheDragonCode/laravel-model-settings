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
    (new User)->defaultSettings()->set('foo', 222);

    $user1->settings()->set('foo', 333);
    $user2->settings()->set('bar', 444);
    $user3->settings()->set('baz', 555);

    $recorder->start();

    $users = User::query()
        ->with('modelSettings')
        ->get()
        ->keyBy('id');

    //dd(
    //    $recorder->queries()
    //);

    $result1 = $users[$user1->id]->settings()->all()->sortKeys()->toArray();
    $result2 = $users[$user2->id]->settings()->all()->sortKeys()->toArray();
    $result3 = $users[$user3->id]->settings()->all()->sortKeys()->toArray();

    expect($result1)->toBe([
        'foo'    => 333,
        'qwerty' => 111,
    ]);

    expect($result2)->toBe([
        'bar'    => 444,
        'foo'    => 222,
        'qwerty' => 111,
    ]);

    expect($result3)->toBe([
        'baz'    => 555,
        'foo'    => 222,
        'qwerty' => 111,
    ]);

    expect($recorder->calls())->toBe(2);
});
