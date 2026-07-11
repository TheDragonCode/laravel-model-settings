<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class PriorityScope
{
    public function __construct(
        protected string $table,
        protected int|string $id,
    ) {}

    public function __invoke(Builder $builder): void
    {
        $builder
            ->select($this->qualifyColumn('*'))
            ->leftJoin(
                $this->table . ' as overrides',
                fn (JoinClause $join) => $join
                    ->on('overrides.item_type', $this->qualifyColumn('item_type'))
                    ->on('overrides.key', $this->qualifyColumn('key'))
                    ->where('overrides.item_id', $this->id)
            )
            ->where(
                fn (Builder $query) => $query
                    ->where($this->qualifyColumn('item_id'), $this->id)
                    ->orWhere(
                        fn (Builder $query) => $query
                            ->where($this->qualifyColumn('item_id'), 0)
                            ->whereNull('overrides.item_id')
                    )
            )
            ->orderByDesc($this->qualifyColumn('item_id'))
            ->orderByDesc($this->qualifyColumn('key'));
    }

    protected function qualifyColumn(string $column): string
    {
        return $this->table . '.' . $column;
    }
}
