<?php

namespace App\Providers;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach ($this->modules() as $module) {
            foreach ($this->classesIn($module, 'Providers') as $provider) {
                if (is_subclass_of($provider, ServiceProvider::class) && ! (new ReflectionClass($provider))->isAbstract()) {
                    $this->app->register($provider);
                }
            }
        }
    }

    public function boot(Router $router): void
    {
        $modules = $this->modules();

        $this->registerFactoryResolver();

        foreach ($modules as $module) {
            $this->loadMigrationsFrom($this->modulePath($module, 'Database/Migrations'));
            $this->loadModuleRoutes($router, $module);
        }

        if ($this->app->runningInConsole()) {
            $commands = collect($this->classesIn('Core', 'Commands'))
                ->filter(function (string $command): bool {
                    return is_subclass_of($command, Command::class)
                        && ! (new ReflectionClass($command))->isAbstract();
                })
                ->values()
                ->all();

            if ($commands !== []) {
                $this->commands($commands);
            }
        }
    }

    /** @return list<string> */
    private function modules(): array
    {
        $path = app_path('Modules');

        if (! is_dir($path)) {
            return [];
        }

        $modules = collect((new Filesystem)->directories($path))
            ->map(fn (string $directory): string => basename($directory))
            ->filter(fn (string $module): bool => ! str_starts_with($module, '.'))
            ->sort()
            ->values()
            ->all();

        return $modules;
    }

    /** @return list<class-string> */
    private function classesIn(string $module, string $directory): array
    {
        $path = $this->modulePath($module, $directory);

        if (! is_dir($path)) {
            return [];
        }

        return collect((new Filesystem)->files($path))
            ->filter(fn ($file): bool => $file->getExtension() === 'php')
            ->map(fn ($file): string => "App\\Modules\\{$module}\\{$directory}\\{$file->getBasename('.php')}")
            ->filter(fn (string $class): bool => class_exists($class))
            ->values()
            ->all();
    }

    private function loadModuleRoutes(Router $router, string $module): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $api = $this->modulePath($module, 'Routes/api.php');
        $web = $this->modulePath($module, 'Routes/web.php');
        $dashboard = $this->modulePath($module, 'Routes/dashboard.php');

        if (is_file($api)) {
            $router->middleware('api')->group($api);
        }

        if (is_file($web)) {
            $router->middleware('web')->group($web);
        }

        if (is_file($dashboard)) {
            $configuration = config('project.routes.dashboard', []);

            $router
                ->middleware(Arr::wrap($configuration['middleware'] ?? ['web']))
                ->prefix($configuration['prefix'] ?? 'dashboard')
                ->group($dashboard);
        }
    }

    private function registerFactoryResolver(): void
    {
        $appNamespace = $this->app->getNamespace();
        $modulePrefix = $appNamespace.'Modules\\';

        Factory::guessFactoryNamesUsing(function (string $modelName) use ($appNamespace, $modulePrefix): string {
            if (Str::startsWith($modelName, $modulePrefix)) {
                $relativeName = Str::after($modelName, $modulePrefix);
                [$module, $model] = array_pad(explode('\\Models\\', $relativeName, 2), 2, null);

                if ($model !== null) {
                    return $modulePrefix.$module.'\\Database\\Factories\\'.$model.'Factory';
                }
            }

            $model = Str::startsWith($modelName, $appNamespace.'Models\\')
                ? Str::after($modelName, $appNamespace.'Models\\')
                : Str::after($modelName, $appNamespace);

            return 'Database\\Factories\\'.$model.'Factory';
        });
    }

    private function modulePath(string $module, string $path = ''): string
    {
        return app_path('Modules/'.$module.($path !== '' ? '/'.$path : ''));
    }
}
