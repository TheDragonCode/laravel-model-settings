<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Models;

use DragonCode\LaravelModelSettings\Casts\PayloadCast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

use function config;

#[Fillable([
    'item_type',
    'item_id',
    'key',
    'payload',
])]
final class Settings extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('model_settings.connection'));
        $this->setTable(config('model_settings.table'));

        parent::__construct($attributes);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'item_id' => 'string',

            'payload' => PayloadCast::class,
        ];
    }
}
