<?php

namespace App\Modules\Core\Commands;

class MakeModuleControllerCommand extends ModuleGeneratorCommand
{
    protected $signature = 'make:module-controller
        {module : The module name}
        {name : The controller class name}
        {--api : Create the controller in Controllers/Api}
        {--web : Create the controller in Controllers/Web}
        {--force : Overwrite the controller if it exists}';

    protected $aliases = ['module:make-controller'];

    protected $description = 'Create a controller in an application module';

    protected string $moduleDirectory = 'Controllers/Api';

    protected string $classSuffix = 'Controller';

    public function handle(): int
    {
        if ($this->option('api') && $this->option('web')) {
            $this->components->error('Choose either --api or --web, not both.');

            return self::INVALID;
        }

        $this->moduleDirectory = $this->option('web') ? 'Controllers/Web' : 'Controllers/Api';

        return parent::handle();
    }

    protected function contents(string $module, string $class, string $namespace, string $qualifiedClass): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use App\Http\Controllers\Controller;

class {$class} extends Controller
{
    //
}
PHP;
    }
}
