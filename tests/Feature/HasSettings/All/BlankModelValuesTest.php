<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseEmpty;

test('blank model values do not override defaults in all results', function (mixed $value) {
    $user = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    (new User)->defaultSettings()->set('foo', 111);

    $user->settings()->set('foo', $value);

    $result1 = $user->settings()->get('foo');
    $result2 = $user->settings()->all()->get('foo');

    expect($result1)->toBe(111);
    expect($result2)->toBe(111);
})->with([
    'null'         => null,
    'empty string' => '',
    'empty array'  => [[]],
]);
