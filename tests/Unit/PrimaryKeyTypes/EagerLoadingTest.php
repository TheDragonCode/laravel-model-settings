<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Workbench\App\Models\SomeId;
use Workbench\App\Models\SomeUlid;
use Workbench\App\Models\SomeUuid;
use Workbench\Database\Factories\SomeIdFactory;
use Workbench\Database\Factories\SomeUlidFactory;
use Workbench\Database\Factories\SomeUuidFactory;

test('eager loading supports primary key type', function (string $modelClass, string $factoryClass): void {
    $models = $factoryClass::new()->count(2)->create();

    $first  = $models->first();
    $second = $models->last();

    (new $modelClass)->defaultSettings()->set(10, 111);
    $first->settings()->set(10, 333);

    $loaded = $modelClass::query()
        ->with('modelSettings')
        ->get()
        ->keyBy(fn (Model $model): string => (string) $model->getKey());

    $first  = $loaded->get((string) $first->getKey());
    $second = $loaded->get((string) $second->getKey());

    expect($first->settings()->get(10))->toBe(333);
    expect($second->settings()->get(10))->toBe(111);
    expect($second->modelSettings->pluck('item_id')->unique()->all())->toBe([(string) $second->getKey()]);
})->with([
    'integer' => [SomeId::class, SomeIdFactory::class],
    'uuid'    => [SomeUuid::class, SomeUuidFactory::class],
    'ulid'    => [SomeUlid::class, SomeUlidFactory::class],
]);
