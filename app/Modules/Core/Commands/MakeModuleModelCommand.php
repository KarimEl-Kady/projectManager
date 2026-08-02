<?php

namespace App\Modules\Core\Commands;

use Illuminate\Support\Str;

class MakeModuleModelCommand extends ModuleGeneratorCommand
{
    protected $signature = 'make:module-model
        {module : The module name}
        {name : The model class name}
        {--factory : Create a model factory}
        {--migration : Create a model migration}
        {--force : Overwrite the model and factory if they exist}';

    protected $aliases = ['module:make-model'];

    protected $description = 'Create a model in an application module';

    protected string $moduleDirectory = 'Models';

    protected function contents(string $module, string $class, string $namespace, string $qualifiedClass): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$class} extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected \$guarded = [];
}
PHP;
    }

    protected function afterCreated(string $module, string $qualifiedClass): void
    {
        if ($this->option('factory')) {
            $this->createFactory($module, $qualifiedClass);
        }

        if ($this->option('migration')) {
            $this->createMigration($module, $qualifiedClass);
        }
    }

    private function createFactory(string $module, string $qualifiedClass): void
    {
        $factoryClass = $qualifiedClass.'Factory';
        $path = $this->classPath($module, 'Database/Factories', $factoryClass);

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->warn("Factory [{$path}] already exists; it was not changed.");

            return;
        }

        $namespace = $this->classNamespace($module, 'Database/Factories', $factoryClass);
        $class = class_basename($factoryClass);
        $model = class_basename($qualifiedClass);
        $modelClass = $this->modelClass($module, $qualifiedClass);
        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, <<<PHP
<?php

namespace {$namespace};

use {$modelClass};
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<{$model}> */
class {$class} extends Factory
{
    protected \$model = {$model}::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            //
        ];
    }
}
PHP);

        $this->components->info("Factory [{$path}] created successfully.");
    }

    private function createMigration(string $module, string $qualifiedClass): void
    {
        $table = Str::snake(Str::pluralStudly(class_basename($qualifiedClass)));
        $directory = app_path("Modules/{$module}/Database/Migrations");
        $baseName = date('Y_m_d_His')."_create_{$table}_table";
        $path = "{$directory}/{$baseName}.php";
        $counter = 1;

        while ($this->files->exists($path)) {
            $path = "{$directory}/{$baseName}_{$counter}.php";
            $counter++;
        }

        $this->files->ensureDirectoryExists($directory);
        $this->files->put($path, <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table): void {
            \$table->id();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP);

        $this->components->info("Migration [{$path}] created successfully.");
    }
}
