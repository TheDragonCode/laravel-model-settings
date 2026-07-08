<?php

declare(strict_types=1);

namespace Workbench\App\Services;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

use function count;

final class QueryRecorder
{
    protected array $queries = [];

    public function start(): void
    {
        $this->reset();
        $this->record();
    }

    public function queries(): array
    {
        return $this->queries;
    }

    public function calls(): int
    {
        return count($this->queries);
    }

    protected function record(): void
    {
        DB::listen(function (QueryExecuted $query): void {
            $this->queries[] = $query->toRawSql();
        });
    }

    protected function reset(): void
    {
        $this->queries = [];
    }
}
