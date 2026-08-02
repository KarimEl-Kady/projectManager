<?php

namespace App\Modules\Core\Commands;

class MakeModuleRepositoryCommand extends ModuleGeneratorCommand
{
    protected $signature = 'make:module-repository
        {module : The module name}
        {name : The repository class name}
        {--model= : The module model class}
        {--force : Overwrite the repository if it exists}';

    protected $aliases = ['module:make-repository'];

    protected $description = 'Create a repository in an application module';

    protected string $moduleDirectory = 'Repositories';

    protected string $classSuffix = 'Repository';

    protected function contents(string $module, string $class, string $namespace, string $qualifiedClass): string
    {
        $modelInput = $this->option('model') ?: str($qualifiedClass)->beforeLast('Repository')->toString();
        $model = $this->normalizeClass($modelInput);
        $modelClass = $this->modelClass($module, $model);
        $modelBase = class_basename($model);

        return <<<PHP
<?php

namespace {$namespace};

use App\Modules\Core\Repositories\BaseRepository;
use {$modelClass};

/** @extends BaseRepository<{$modelBase}> */
class {$class} extends BaseRepository
{
    public function __construct({$modelBase} \$model)
    {
        parent::__construct(\$model);
    }
}
PHP;
    }
}
