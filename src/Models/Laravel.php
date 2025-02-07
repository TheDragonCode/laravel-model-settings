<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Model;

class Laravel extends Model
{
    use HasSettings;

    protected $table = 'laravel';

    protected $attributes = [
        'id' => 1,
    ];
}
