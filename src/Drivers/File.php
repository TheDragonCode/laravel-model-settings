<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Drivers;

use DragonCode\LaravelModelSettings\Services\Cache;
use Illuminate\Container\Attributes\Config;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Storage;

use function serialize;
use function unserialize;

class File extends Driver
{
    public function __construct(
        Cache $cache,
        #[Config('model-settings.repositories.file.disk')]
        protected ?string $disk,
        #[Config('model-settings.repositories.file.directory')]
        protected ?string $directory
    ) {
        parent::__construct($cache);
    }

    protected function getPayload(): array|Arrayable
    {
        if ($this->storage()->exists($this->path())) {
            return unserialize($this->storage()->get($this->path()), ['max_depth' => 128]);
        }

        return [];
    }

    public function apply(array|Arrayable $settings): static
    {
        $settings = $this->merge($this->getPayload(), $settings);

        $this->storage()->put($this->path(), serialize($settings));

        $this->cache()->forget();

        return $this;
    }

    public function clear(): static
    {
        if ($this->storage()->exists($this->path())) {
            $this->storage()->delete($this->path());
        }

        $this->cache()->forget();

        return $this;
    }

    protected function path(): string
    {
        $path = $this->model->getTable() . '/' . $this->model->getKey();

        return $this->directory ? $this->directory . '/' . $path : $path;
    }

    protected function storage(): Filesystem
    {
        return Storage::disk($this->disk);
    }
}
