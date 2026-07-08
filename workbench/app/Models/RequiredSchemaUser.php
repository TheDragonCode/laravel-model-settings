<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Workbench\App\Settings\RequiredParamSettings;

class RequiredSchemaUser extends User
{
    protected $table = 'users';

    public function settingsSchema(): ?string
    {
        return RequiredParamSettings::class;
    }
}
