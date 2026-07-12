<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SomeId extends Model
{
    use HasFactory;
    use HasSettings;

    protected $table = 'some_ids';

    public $timestamps = false;
}
