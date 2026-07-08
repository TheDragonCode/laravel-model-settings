<?php

declare(strict_types=1);

namespace Workbench\App\Settings;

final class RequiredParamSettings
{
    public function __construct(
        public string $currency,
        public string $timezone = 'UTC',
    ) {}
}
