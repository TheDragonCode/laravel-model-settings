<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Scopes;

use DragonCode\LaravelModelSettings\Concerns\HasIdentifier;
use DragonCode\LaravelModelSettings\Concerns\HasModelResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class PriorityScope
{
    use HasModelResolver;
    use HasIdentifier;

    public function __construct(
        protected Model $model,
        protected int|string $id,
    ) {}

    public function __invoke(Builder $builder): void
    {
        $column = $this->modelIdColumn($this->model);

        $builder
            ->select($this->qualifyColumn('*'))
            ->leftJoin(
                $this->table() . ' as overrides',
                fn (JoinClause $join) => $join
                    ->on('overrides.item_type', $this->qualifyColumn('item_type'))
                    ->on('overrides.key', $this->qualifyColumn('key'))
                    ->where('overrides.' . $column, $this->id)
            )
            ->where(fn (Builder $query) => $query
                ->where($this->qualifyColumn($column), $this->id)
                ->orWhere(fn (Builder $query) => $query
                    ->where($this->qualifyColumn($column), $this->defaultId())
                    ->whereNull('overrides.' . $column)
                )
            )
            ->orderByDesc($this->qualifyColumn($column))
            ->orderBy($this->qualifyColumn('key'));
    }

    protected function qualifyColumn(string $column): string
    {
        return $this->settingsModel()->qualifyColumn($column);
    }

    protected function table(): string
    {
        return $this->settingsModel()->getTable();
    }
}
