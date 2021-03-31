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

    private function normalizePath(string $path): string
    {
        return trim(preg_replace('/[\/|\\\]+/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    private function explodePath(string $path): Collection
    {
        return collect(explode(DIRECTORY_SEPARATOR, $this->normalizePath($path)));
    }

    private function normalizeNamespace(string $dirname): string
    {
        return $this
            ->explodePath($this->namespace . DIRECTORY_SEPARATOR . $dirname)
            ->map(fn($namespace) => Str::ucfirst($namespace))
            ->join(DIRECTORY_SEPARATOR);
    }

    private function normalizePrefix(string $dirname): string
    {
        return $this
            ->explodePath($dirname)
            ->join(self::ROUTE_SEPARATOR);
    }

    public function map(): void
    {
        $modules = Arr::wrap(config('routes.modules'));

        foreach ($modules as $module) {
            $routeConfig = new RouteModuleValue($module);

            $files = File::allFiles(base_path('routes' . DIRECTORY_SEPARATOR . $routeConfig->getDirectory()));

            foreach ($files as $file) {
                $path = Str::replaceFirst(base_path(), null, $file->getRealPath());

                $dirname = Str::replaceFirst('routes' . DIRECTORY_SEPARATOR . $routeConfig->getDirectory(), null, File::dirname($path));

                $namespace = $routeConfig->extendNamespaceFromFolders()
                    ? $this->normalizeNamespace($routeConfig->getNamespace() . DIRECTORY_SEPARATOR . $dirname)
                    : $routeConfig->getNamespace();

                $prefix = $routeConfig->extendPrefixFromFolders()
                    ? $this->normalizePrefix($routeConfig->getPrefix() . self::ROUTE_SEPARATOR . $dirname)
                    : $routeConfig->getPrefix();

                $this->mapRoutes(
                    $path,
                    $namespace,
                    $prefix,
                    $routeConfig->getMiddleware()
                );
            }
        }
    }
}
