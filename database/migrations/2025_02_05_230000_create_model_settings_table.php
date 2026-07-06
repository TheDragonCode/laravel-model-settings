<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->connection()->create($this->table(), static function (Blueprint $table) {
            $table->id();

            $table->string('item_type');
            $table->unsignedBigInteger('item_id');

            $table->string('key');
            $table->jsonb('payload');

            $table->smallInteger('sort_order');

            $table->timestamps();

            $table->unique(['item_type', 'item_id', 'key']);
        });
    }

    protected function connection(): Builder
    {
        return Schema::connection(
            config('model-settings.database.connection')
        );
    }

    protected function table(): string
    {
        return config()->string('model-settings.database.table');
    }
};
