<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Workbench\App\Services\Snapshot;

expect()->pipe('toMatchSnapshot', function (Closure $next) {
    if (is_string($this->value) && is_a($this->value, Model::class, true)) {
        $this->value = new Snapshot([
            'model' => $this->value,
            'items' => getTableData($this->value),
        ]);
    }

    return $next();
});
