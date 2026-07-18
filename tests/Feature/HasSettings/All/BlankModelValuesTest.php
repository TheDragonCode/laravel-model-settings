<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;

test('model values override defaults exactly in lazy and eager results', function (mixed $value): void {
    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->set('foo', 'default');

    $user->settings()->set('foo', $value);

    expect($user->settings()->has('foo'))->toBeTrue()
        ->and($user->settings()->get('foo'))->toBe($value)
        ->and($user->settings()->all()->has('foo'))->toBeTrue()
        ->and($user->settings()->all()->get('foo'))->toBe($value);

    $eager = User::query()->with('modelSettings')->findOrFail($user->getKey());

    expect($eager->settings()->has('foo'))->toBeTrue()
        ->and($eager->settings()->get('foo'))->toBe($value)
        ->and($eager->settings()->all()->has('foo'))->toBeTrue()
        ->and($eager->settings()->all()->get('foo'))->toBe($value);

    assertDatabaseCount(Settings::class, 2);
})->with([
    'null'         => null,
    'empty string' => '',
    'whitespace'   => '   ',
    'empty array'  => [[]],
    'zero'         => 0,
    'false'        => false,
    'normal value' => 'value',
]);
