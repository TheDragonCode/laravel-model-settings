<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Exceptions\BulkMutationException;
use Workbench\App\Models\User;

test('bulk mutation exceptions describe the failed operation and preserve the cause', function (
    string $operation,
    bool $defaultScope,
    string $scope,
): void {
    $owner   = new User;
    $failure = new RuntimeException('Underlying failure.');

    $exception = BulkMutationException::$operation($owner, $defaultScope, $failure);

    expect($exception->getMessage())
        ->toBe("Model settings [$operation] failed for [Workbench\\App\\Models\\User] in [$scope] scope.");
    expect($exception->getPrevious())->toBe($failure);
})->with([
    'setMany model scope'      => ['setMany', false, 'model'],
    'setMany default scope'    => ['setMany', true, 'default'],
    'forgetMany model scope'   => ['forgetMany', false, 'model'],
    'forgetMany default scope' => ['forgetMany', true, 'default'],
    'purge model scope'        => ['purge', false, 'model'],
    'purge default scope'      => ['purge', true, 'default'],
]);
