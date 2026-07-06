<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Models;

use Illuminate\Database\Eloquent\Model;

use function config;

class Settings extends Model
{
    protected $fillable = [
        'item_type',
        'item_id',
        'key',
        'payload',
        'sort_order',
    ];

    protected $attributes = [
        'sort_order' => 1,
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('model-settings.repositories.database.connection'));
        $this->setTable(config('model-settings.repositories.database.table'));

        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'payload'    => 'json',
            'sort_order' => 'int',
        ];
    }
}
