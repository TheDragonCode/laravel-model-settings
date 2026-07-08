<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Workbench\App\Settings\UserSettings;

class SchemaUser extends User
{
    protected $table = 'users';

    public function settingsSchema(): ?string
    {
        return UserSettings::class;
    }
}
