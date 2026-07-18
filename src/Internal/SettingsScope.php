<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Internal;

use DragonCode\LaravelModelSettings\Enums\IdentifierEnum;
use DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException;
use Illuminate\Database\Eloquent\Model;

use function is_int;

final readonly class SettingsScope
{
    protected function __construct(
        protected Model $owner,
        protected SettingsScopeTypeEnum $type,
    ) {}

    public static function defaults(Model $owner): self
    {
        return new self($owner, SettingsScopeTypeEnum::Default);
    }

    public static function model(Model $owner): self
    {
        return new self($owner, SettingsScopeTypeEnum::Model);
    }

    public function owner(): Model
    {
        return $this->owner;
    }

    public function itemType(): string
    {
        return $this->owner->getMorphClass();
    }

    public function itemId(): int|string|null
    {
        if ($this->isDefault()) {
            return IdentifierEnum::Default->value;
        }

        $identifier = $this->owner->getKey();

        if ($identifier === null || is_int($identifier)) {
            return $identifier;
        }

        return (string) $identifier;
    }

    public function requiredItemId(): int|string
    {
        return $this->itemId()
            ?? throw InvalidSettingsOwnerException::unsaved($this->owner);
    }

    public function isDefault(): bool
    {
        return $this->type === SettingsScopeTypeEnum::Default;
    }

    public function isReadable(): bool
    {
        return $this->isDefault()
            || ($this->owner->exists && $this->itemId() !== null);
    }

    public function ensureMutable(): void
    {
        if ($this->isDefault()) {
            return;
        }

        if (! $this->isReadable()) {
            throw InvalidSettingsOwnerException::unsaved($this->owner);
        }

        if ((string) $this->requiredItemId() === IdentifierEnum::Default->value) {
            throw InvalidSettingsOwnerException::reservedIdentifier($this->owner);
        }
    }
}
