<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Model;

final class SomeInteger extends Model
{
    use HasSettings;

    protected $table = 'some_integers';

    protected $casts = [
        'id' => 'integer',
    ];

    public $incrementing = false;

    public $timestamps = false;
}
