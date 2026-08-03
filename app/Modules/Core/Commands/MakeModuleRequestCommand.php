<?php

namespace App\Modules\Core\Commands;

class MakeModuleRequestCommand extends ModuleGeneratorCommand
{
    protected $signature = 'make:module-request
        {module : The module name}
        {name : The request class name}
        {--fetch : Extend FetchRequest instead of BaseRequest}
        {--force : Overwrite the request if it exists}';

    protected $aliases = ['module:make-request'];

    protected $description = 'Create a request in an application module';

    protected string $moduleDirectory = 'Requests';

    protected string $classSuffix = 'Request';

    protected function contents(string $module, string $class, string $namespace, string $qualifiedClass): string
    {
        $parent = $this->option('fetch') ? 'FetchRequest' : 'BaseRequest';
        $parentClass = "App\\Modules\\Core\\Requests\\{$parent}";

        return <<<PHP
<?php

namespace {$namespace};

use {$parentClass};

class {$class} extends {$parent}
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            //
        ]);
    }
}
PHP;
    }
}
