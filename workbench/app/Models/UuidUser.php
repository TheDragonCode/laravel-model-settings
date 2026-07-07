<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UuidUser extends Model
{
    use HasSettings;
    use HasUuids;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
