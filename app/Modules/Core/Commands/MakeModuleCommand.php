<?php

namespace App\Modules\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Throwable;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module
        {name? : The module name}
        {--entity= : The primary model/class name (defaults to the singular module name)}
        {--all : Generate the complete module stack without asking component questions}
        {--plain : Generate only the module directory structure}
        {--model : Generate a model}
        {--factory : Generate a factory with the model}
        {--migration : Generate a migration with the model}
        {--repository : Generate a repository}
        {--service : Generate a service}
        {--api-controller : Generate an API controller}
        {--web-controller : Generate a web controller}
        {--request : Generate a standard request}
        {--fetch-request : Generate a fetch request}
        {--resource : Generate an API resource}';

    protected $aliases = ['module:make'];

    protected $description = 'Create a new application module';

    /** @var list<string> */
    private array $directories = [
        'Controllers/Api',
        'Controllers/Web',
        'Commands',
        'Database/Factories',
        'Database/Migrations',
        'Database/Seeders',
        'Models',
        'Providers',
        'Repositories',
        'Requests',
        'Resources',
        'Routes',
        'Services',
        'Tests/Feature',
        'Tests/Unit',
    ];

    /** @var list<string> */
    private array $componentOptions = [
        'model',
        'factory',
        'migration',
        'repository',
        'service',
        'api-controller',
        'web-controller',
        'request',
        'fetch-request',
        'resource',
    ];

    public function __construct(private Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $input = trim((string) ($this->argument('name') ?: ''));

        if ($input === '' && $this->input->isInteractive()) {
            $input = trim((string) $this->components->ask('What is the module name?'));
        }

        if (! preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $input)) {
            $this->components->error('The module name must start with a letter and contain only letters, numbers, and underscores.');

            return self::INVALID;
        }

        $module = Str::studly($input);
        $path = app_path("Modules/{$module}");

        if ($this->files->isDirectory($path)) {
            $this->components->error("Module [{$module}] already exists.");

            return self::FAILURE;
        }

        if ($this->option('all') && $this->option('plain')) {
            $this->components->error('The --all and --plain options cannot be used together.');

            return self::INVALID;
        }

        $entityInput = trim((string) ($this->option('entity') ?: Str::singular($module)));

        if ($this->shouldRunWizard() && $this->option('entity') === null) {
            $entityInput = trim((string) $this->components->ask(
                'What is the primary class/model name?',
                Str::singular($module),
            ));
        }

        if (! preg_match('/^[A-Za-z][A-Za-z0-9_]*(?:[\\\\\/][A-Za-z][A-Za-z0-9_]*)*$/', $entityInput)) {
            $this->components->error('The primary class name must be a valid PHP class name and may include subdirectories.');

            return self::INVALID;
        }

        $entity = collect(preg_split('/[\\\\\/]+/', $entityInput))
            ->map(fn (string $segment): string => Str::studly($segment))
            ->implode('\\');
        $components = $this->componentsToGenerate();

        if ($this->shouldRunWizard()) {
            $selected = collect($components)
                ->filter()
                ->keys()
                ->map(fn (string $component): string => Str::headline($component))
                ->all();

            $this->components->info("Module: {$module} | Primary class: {$entity}");
            $this->components->bulletList($selected !== [] ? $selected : ['Directory structure only']);

            if (! $this->components->confirm('Create the module now?', true)) {
                $this->components->warn('Module creation cancelled. No files were created.');

                return self::SUCCESS;
            }
        }

        try {
            $this->createStructure($module, $path);
            $this->generateComponents($module, $entity, $components);
        } catch (Throwable $exception) {
            $this->files->deleteDirectory($path);
            $this->components->error('Module creation failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Module [{$module}] created successfully at [{$path}].");

        return self::SUCCESS;
    }

    private function providerContents(string $module): string
    {
        $namespace = "App\\Modules\\{$module}\\Providers";

        return <<<PHP
<?php

namespace {$namespace};

use Illuminate\Support\ServiceProvider;

class {$module}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register module bindings here.
    }

    public function boot(): void
    {
        // Bootstrap module services here.
    }
}
PHP;
    }

    private function shouldRunWizard(): bool
    {
        return $this->input->isInteractive()
            && ! $this->option('all')
            && ! $this->option('plain')
            && ! collect($this->componentOptions)->contains(fn (string $option): bool => (bool) $this->option($option));
    }

    /** @return array<string, bool> */
    private function componentsToGenerate(): array
    {
        if ($this->option('plain')) {
            return array_fill_keys($this->componentOptions, false);
        }

        if ($this->option('all')) {
            return array_fill_keys($this->componentOptions, true);
        }

        if ($this->shouldRunWizard()) {
            $model = $this->components->confirm('Create a model?', true);
            $factory = $model && $this->components->confirm('Create a model factory?', true);
            $migration = $model && $this->components->confirm('Create a migration?', true);
            $repository = $model && $this->components->confirm('Create a repository?', true);
            $service = $repository && $this->components->confirm('Create a service?', true);

            $components = [
                'model' => $model,
                'factory' => $factory,
                'migration' => $migration,
                'repository' => $repository,
                'service' => $service,
                'api-controller' => $this->components->confirm('Create an API controller?', true),
                'web-controller' => $this->components->confirm('Create a web controller?', false),
                'request' => $this->components->confirm('Create a standard request?', true),
                'fetch-request' => $this->components->confirm('Create a fetch request?', true),
                'resource' => $this->components->confirm('Create an API resource?', true),
            ];
        } else {
            $components = collect($this->componentOptions)
                ->mapWithKeys(fn (string $option): array => [$option => (bool) $this->option($option)])
                ->all();
        }

        if ($components['factory'] || $components['migration'] || $components['repository']) {
            $components['model'] = true;
        }

        if ($components['service']) {
            $components['repository'] = true;
            $components['model'] = true;
        }

        return $components;
    }

    private function createStructure(string $module, string $path): void
    {
        foreach ($this->directories as $directory) {
            $directoryPath = "{$path}/{$directory}";
            $this->files->ensureDirectoryExists($directoryPath);
            $this->files->put("{$directoryPath}/.gitkeep", '');
        }

        $this->files->put("{$path}/Providers/{$module}ServiceProvider.php", $this->providerContents($module));
        $this->files->delete("{$path}/Providers/.gitkeep");

        foreach (['api', 'web', 'dashboard'] as $route) {
            $this->files->put("{$path}/Routes/{$route}.php", "<?php\n\n// {$module} {$route} routes.\n");
        }

        $this->files->delete("{$path}/Routes/.gitkeep");
    }

    /** @param array<string, bool> $components */
    private function generateComponents(string $module, string $entity, array $components): void
    {
        if ($components['model']) {
            $this->runGenerator('make:module-model', [
                'module' => $module,
                'name' => $entity,
                '--factory' => $components['factory'],
                '--migration' => $components['migration'],
            ]);
        }

        if ($components['repository']) {
            $this->runGenerator('make:module-repository', ['module' => $module, 'name' => $entity]);
        }

        if ($components['service']) {
            $this->runGenerator('make:module-service', ['module' => $module, 'name' => $entity]);
        }

        if ($components['api-controller']) {
            $this->runGenerator('make:module-controller', ['module' => $module, 'name' => $entity, '--api' => true]);
        }

        if ($components['web-controller']) {
            $this->runGenerator('make:module-controller', ['module' => $module, 'name' => $entity, '--web' => true]);
        }

        if ($components['request']) {
            $this->runGenerator('make:module-request', [
                'module' => $module,
                'name' => $this->prefixClass($entity, 'Store'),
            ]);
        }

        if ($components['fetch-request']) {
            $this->runGenerator('make:module-request', [
                'module' => $module,
                'name' => $this->prefixClass($entity, 'Fetch'),
                '--fetch' => true,
            ]);
        }

        if ($components['resource']) {
            $this->runGenerator('make:module-resource', ['module' => $module, 'name' => $entity]);
        }
    }

    /** @param array<string, mixed> $arguments */
    private function runGenerator(string $command, array $arguments): void
    {
        if ($this->call($command, $arguments) !== self::SUCCESS) {
            throw new \RuntimeException("Generator [{$command}] failed.");
        }
    }

    private function prefixClass(string $class, string $prefix): string
    {
        $position = strrpos($class, '\\');

        return $position === false
            ? $prefix.$class
            : substr($class, 0, $position + 1).$prefix.substr($class, $position + 1);
    }
}
