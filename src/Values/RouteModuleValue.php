<?php

namespace GoldcarrotLaravel\Values;

use GoldcarrotLaravel\Exceptions\InvalidConfigurationException;
use Illuminate\Support\Arr;

class RouteModuleValue
{
    private bool $extendNamespaceFromFolders;
    private bool $extendPrefixFromFolders;

    private string $directory;
    private string $namespace;
    private string $prefix;
    private array $middleware;

    public function __construct(array $config)
    {
        $this->extendNamespaceFromFolders = Arr::get($config, 'extendNamespaceFromFolders', true);
        $this->extendPrefixFromFolders = Arr::get($config, 'extendPrefixFromFolders', true);
        $this->namespace = Arr::get($config, 'namespace');
        $this->prefix = Arr::get($config, 'prefix');
        $this->middleware = Arr::wrap(Arr::get($config, 'middleware', []));

        if (Arr::has($config, 'directory')) {
            $this->directory = Arr::get($config, 'directory');
        } else {
            throw new InvalidConfigurationException('Directory must be set in each module');
        }
    }

    public function extendNamespaceFromFolders(): bool
    {
        return $this->extendNamespaceFromFolders;
    }

    public function extendPrefixFromFolders(): bool
    {
        return $this->extendPrefixFromFolders;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}