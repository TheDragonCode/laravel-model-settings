<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use DragonCode\LaravelModelSettings\Services\SettingsService;
use Workbench\App\Settings\UserSettings;

/**
 * @method SettingsService<UserSettings> settings()
 */
class SchemaUser extends User
{
    protected $table = 'users';

    public function settingsSchema(): ?string
    {
        return UserSettings::class;
    }
}
