<?php

namespace App\Modules\Core\Commands;

class MakeModuleServiceCommand extends ModuleGeneratorCommand
{
    protected $signature = 'make:module-service
        {module : The module name}
        {name : The service class name}
        {--repository= : The module repository class}
        {--force : Overwrite the service if it exists}';

    protected $aliases = ['module:make-service'];

    protected $description = 'Create a service in an application module';

    protected string $moduleDirectory = 'Services';

    protected string $classSuffix = 'Service';

    protected function contents(string $module, string $class, string $namespace, string $qualifiedClass): string
    {
        $repositoryInput = $this->option('repository')
            ?: str($qualifiedClass)->beforeLast('Service')->append('Repository')->toString();
        $repository = $this->normalizeClass($repositoryInput, 'Repository');
        $repositoryClass = 'App\\Modules\\'.$module.'\\Repositories\\'.$repository;
        $repositoryBase = class_basename($repository);

        return <<<PHP
<?php

namespace {$namespace};

use App\Modules\Core\Services\BaseService;
use {$repositoryClass};

/** @extends BaseService<{$repositoryBase}> */
class {$class} extends BaseService
{
    public function __construct({$repositoryBase} \$repository)
    {
        parent::__construct(\$repository);
    }
}
PHP;
    }
}
