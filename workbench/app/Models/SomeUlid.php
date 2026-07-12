<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SomeUlid extends Model
{
    use HasFactory;
    use HasSettings;
    use HasUlids;

    protected $table = 'some_ulids';

    public $timestamps = false;

    protected $primaryKey = 'ulid';
}
