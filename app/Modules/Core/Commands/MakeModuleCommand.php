<?php

namespace App\Modules\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : The module name}';

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

    public function __construct(private Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $input = trim((string) $this->argument('name'));

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
        $this->components->info("Module [{$module}] created successfully at [{$path}].");

        return self::SUCCESS;
    }

    private function providerContents(string $module): string
    {
        return <<<PHP
<?php

namespace App\Modules\{$module}\Providers;

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
}
