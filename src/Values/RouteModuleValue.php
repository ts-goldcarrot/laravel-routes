<?php

namespace GoldcarrotLaravel\Values;

use GoldcarrotLaravel\Exceptions\InvalidConfigurationException;
use Illuminate\Support\Arr;

class RouteModuleValue
{
    private bool $extendNamespaceFromFolders = true;
    private bool $extendPrefixFromFolders = true;

    private $directory;
    private $namespace;
    private $prefix;
    private array $middleware;

    public function __construct(array $config)
    {
        $this->extendNamespaceFromFolders = Arr::get($config, 'extendNamespaceFromFolders', $this->extendNamespaceFromFolders);
        $this->extendPrefixFromFolders = Arr::get($config, 'extendPrefixFromFolders', $this->extendPrefixFromFolders);

        $this->namespace = Arr::get($config, 'namespace');
        $this->prefix = Arr::get($config, 'prefix');
        $this->middleware = Arr::wrap(Arr::get($config, 'middleware'));

        if (Arr::has($config, 'directory')) {
            $this->directory = Arr::get($config, 'directory');
        } else {
            throw new InvalidConfigurationException('Directory must be set in each module');
        }
    }

    public function extendNamespaceFromFolders()
    {
        return $this->extendNamespaceFromFolders;
    }

    public function extendPrefixFromFolders()
    {
        return $this->extendPrefixFromFolders;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}