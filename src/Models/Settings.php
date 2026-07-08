<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Models;

use Illuminate\Database\Eloquent\Model;

use function config;

final class Settings extends Model
{
    protected $fillable = [
        'item_type',
        'item_id',
        'key',
        'payload',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('model_settings.connection'));
        $this->setTable(config('model_settings.table'));

        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'payload' => 'json',
        ];
    }
}
