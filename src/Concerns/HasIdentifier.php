<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

trait HasIdentifier
{
    use HasModelResolver;

    protected int|string|null $defaultIdentifier = null;

    protected function defaultId(): int|string
    {
        return $this->defaultIdentifier ??= $this->settingsModel()->getKeyType() === 'int' ? 0 : '0';
    }
}
