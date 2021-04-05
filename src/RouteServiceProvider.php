<?php

namespace GoldcarrotLaravel;

use Arr;
use GoldcarrotLaravel\Values\RouteModuleValue;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    private const ROUTE_SEPARATOR = '/';
    private const NAMESPACE_SEPARATOR = "\\";

    public function __construct($app)
    {
        $this->namespace = config('routes.namespace');
        parent::__construct($app);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/routes.php' => config_path('routes.php'),
        ], 'config');

        parent::boot();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/routes.php',
            'routes'
        );

        parent::register();
    }

    private function mapRoutes($path, $namespace = null, $prefix = null, $middleware = []): void
    {
        Route::namespace($namespace)
            ->middleware($middleware)
            ->prefix($prefix)
            ->group(base_path($path));
    }

    private function explodePath(string $path): Collection
    {
        $pieces = collect();

        while ($piece = basename($path)) {
            $path = str_replace($piece, null, $path);
            $pieces->push($piece);
        }

        return $pieces->reverse()->values();
    }

    private function pathWithout($path, $without)
    {
        $path = $this->explodePath($path)->join(DIRECTORY_SEPARATOR);
        $without = $this->explodePath($without)->join(DIRECTORY_SEPARATOR);

        return $this->explodePath(str_replace($without, null, $path))->join(DIRECTORY_SEPARATOR);
    }

    private function normalizeNamespace(RouteModuleValue $config, string $dirname): string
    {
        $namespace = $config->getNamespace();

        if ($config->extendNamespaceFromFolders()) {
            $namespace .= self::NAMESPACE_SEPARATOR . $dirname;
        }

        return $this
            ->explodePath($this->namespace . DIRECTORY_SEPARATOR . $namespace)
            ->map(fn($piece) => Str::ucfirst($piece))
            ->join(self::NAMESPACE_SEPARATOR);
    }

    private function normalizePrefix(RouteModuleValue $config, string $dirname): string
    {
        $prefix = $config->getPrefix();
        if ($config->extendPrefixFromFolders()) {
            $prefix .= self::ROUTE_SEPARATOR . $dirname;
        }

        return $this
            ->explodePath($prefix)
            ->join(self::ROUTE_SEPARATOR);
    }

    public function map(): void
    {
        $modules = Arr::wrap(config('routes.modules'));

        foreach ($modules as $module) {
            $routeConfig = new RouteModuleValue($module);

            $files = File::allFiles(base_path('routes' . DIRECTORY_SEPARATOR . $routeConfig->getDirectory()));

            foreach ($files as $file) {
                $path = $this->pathWithout($file->getRealPath(), base_path());
                $dirname = $this->pathWithout(File::dirname($path), 'routes' . DIRECTORY_SEPARATOR . $routeConfig->getDirectory());

                $this->mapRoutes(
                    $path,
                    $this->normalizeNamespace($routeConfig, $dirname),
                    $this->normalizePrefix($routeConfig, $dirname),
                    $routeConfig->getMiddleware()
                );
            }
        }
    }
}
