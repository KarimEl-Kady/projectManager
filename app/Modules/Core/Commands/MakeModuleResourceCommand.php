<?php

namespace App\Modules\Core\Commands;

class MakeModuleResourceCommand extends ModuleGeneratorCommand
{
    protected $signature = 'make:module-resource
        {module : The module name}
        {name : The resource class name}
        {--force : Overwrite the resource if it exists}';

    protected $aliases = ['module:make-resource'];

    protected $description = 'Create an API resource in an application module';

    protected string $moduleDirectory = 'Resources';

    protected string $classSuffix = 'Resource';

    protected function contents(string $module, string $class, string $namespace, string $qualifiedClass): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {$class} extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request \$request): array
    {
        return parent::toArray(\$request);
    }
}
PHP;
    }
}
