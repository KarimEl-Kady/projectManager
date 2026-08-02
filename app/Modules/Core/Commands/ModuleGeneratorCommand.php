<?php

namespace App\Modules\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

abstract class ModuleGeneratorCommand extends Command
{
    protected string $moduleDirectory;

    protected string $classSuffix = '';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $module = $this->normalizeModule((string) $this->argument('module'));
            $class = $this->normalizeClass((string) $this->argument('name'), $this->classSuffix);
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::INVALID;
        }

        if (! $this->files->isDirectory(app_path("Modules/{$module}"))) {
            $this->components->error("Module [{$module}] does not exist. Run php artisan make:module {$module} first.");

            return self::FAILURE;
        }

        $path = $this->classPath($module, $this->moduleDirectory, $class);

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->error("{$this->classLabel()} [{$path}] already exists.");

            return self::FAILURE;
        }

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $this->contents(
            $module,
            class_basename($class),
            $this->classNamespace($module, $this->moduleDirectory, $class),
            $class,
        ));

        $this->afterCreated($module, $class);
        $this->components->info("{$this->classLabel()} [{$path}] created successfully.");

        return self::SUCCESS;
    }

    abstract protected function contents(
        string $module,
        string $class,
        string $namespace,
        string $qualifiedClass,
    ): string;

    protected function afterCreated(string $module, string $qualifiedClass): void
    {
        // Hook for generators that create companion files.
    }

    protected function classLabel(): string
    {
        return Str::headline($this->moduleDirectory);
    }

    protected function normalizeModule(string $module): string
    {
        $module = trim($module);

        if (! preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $module)) {
            throw new InvalidArgumentException('The module name must start with a letter and contain only letters, numbers, and underscores.');
        }

        return Str::studly($module);
    }

    protected function normalizeClass(string $name, string $suffix = ''): string
    {
        $name = trim($name, ' \\/');

        if (! preg_match('/^[A-Za-z][A-Za-z0-9_]*(?:[\\\\\/][A-Za-z][A-Za-z0-9_]*)*$/', $name)) {
            throw new InvalidArgumentException('The class name must be a valid PHP class name and may include subdirectories.');
        }

        $segments = array_map(Str::studly(...), preg_split('/[\\\\\/]+/', $name));
        $last = array_pop($segments);

        if ($suffix !== '' && ! Str::endsWith($last, $suffix)) {
            $last .= $suffix;
        }

        return implode('\\', [...$segments, $last]);
    }

    protected function classNamespace(string $module, string $directory, string $qualifiedClass): string
    {
        $base = 'App\\Modules\\'.$module.'\\'.str_replace('/', '\\', $directory);
        $position = strrpos($qualifiedClass, '\\');

        return $position === false ? $base : $base.'\\'.substr($qualifiedClass, 0, $position);
    }

    protected function classPath(string $module, string $directory, string $qualifiedClass): string
    {
        return app_path('Modules/'.$module.'/'.$directory.'/'.str_replace('\\', '/', $qualifiedClass).'.php');
    }

    protected function modelClass(string $module, string $qualifiedClass): string
    {
        return 'App\\Modules\\'.$module.'\\Models\\'.$qualifiedClass;
    }
}
