<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    use HasSettings;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
