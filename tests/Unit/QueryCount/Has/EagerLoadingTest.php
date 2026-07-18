<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder;
use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('has preserves lazy and eager query bounds', function (bool $with, int $queries): void {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();
    $user3 = UserFactory::new()->create();

    (new User)->defaultSettings()->set('nullable', null);
    $user1->settings()->set('nullable', null);

    $recorder = new QueryRecorder;
    $recorder->start();

    $users = User::query()
        ->when($with, fn (Builder $query) => $query->with('modelSettings'))
        ->get()
        ->keyBy('id');

    expect($users[$user1->id]->settings()->has('nullable'))->toBeTrue()
        ->and($users[$user2->id]->settings()->has('nullable'))->toBeTrue()
        ->and($users[$user3->id]->settings()->has('missing'))->toBeFalse()
        ->and($recorder->calls())->toBe($queries);
})->with([
    'eager' => [true, 2],
    'lazy'  => [false, 4],
]);
