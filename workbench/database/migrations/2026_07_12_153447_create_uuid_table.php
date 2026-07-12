<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('some_uuids', function (Blueprint $table) {
            $table->uuid()->primary();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('some_uuids');
    }
};
