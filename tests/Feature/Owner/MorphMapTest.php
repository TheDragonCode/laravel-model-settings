<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\SomeInteger;

test('morph maps preserve default and zero-owner override scopes', function (): void {
    $morphMap = Relation::morphMap();

    Relation::morphMap(['integer-setting-owner' => SomeInteger::class], false);

    try {
        DB::table('some_integers')->insert([
            ['id' => 0],
            ['id' => 1],
        ]);

        $zero = SomeInteger::query()->findOrFail(0);
        $one  = SomeInteger::query()->findOrFail(1);

        (new SomeInteger)->defaultSettings()->setMany([
            'foo' => 111,
            'bar' => 222,
        ]);

        $zero->settings()->set('foo', 333);
        $one->settings()->set('bar', 444);

        $owners = SomeInteger::query()
            ->with('modelSettings')
            ->get()
            ->keyBy('id');

        expect($owners[0]->settings()->all()->sortKeys()->all())->toBe([
            'bar' => 222,
            'foo' => 333,
        ])->and($owners[1]->settings()->all()->sortKeys()->all())->toBe([
            'bar' => 444,
            'foo' => 111,
        ])->and($owners[0]->modelSettings->pluck('is_default')->sort()->values()->all())->toBe([false, true])
            ->and($owners[1]->modelSettings->pluck('is_default')->sort()->values()->all())->toBe([false, true])
            ->and(Settings::query()->where('item_type', 'integer-setting-owner')->count())->toBe(4);
    } finally {
        Relation::morphMap($morphMap, false);
    }
});
