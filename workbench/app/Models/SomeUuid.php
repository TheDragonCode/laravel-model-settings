<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SomeUuid extends Model
{
    use HasFactory;
    use HasSettings;
    use HasUuids;

    protected $table = 'some_uuids';

    protected $primaryKey = 'uuid';

    public $timestamps = false;
}
