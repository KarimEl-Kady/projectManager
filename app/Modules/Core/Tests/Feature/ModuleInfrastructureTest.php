<?php

namespace App\Modules\Core\Tests\Feature;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModuleInfrastructureTest extends TestCase
{
    public function test_module_commands_are_registered(): void
    {
        $this->artisan('make:module --help')->assertSuccessful();
        $this->artisan('make:module-controller --help')->assertSuccessful();
        $this->artisan('make:module-model --help')->assertSuccessful();
        $this->artisan('make:module-request --help')->assertSuccessful();
        $this->artisan('make:module-resource --help')->assertSuccessful();
        $this->artisan('make:module-service --help')->assertSuccessful();
        $this->artisan('make:module-repository --help')->assertSuccessful();
    }

    public function test_module_factory_names_are_resolved_to_the_module(): void
    {
        $factory = Factory::resolveFactoryName('App\\Modules\\Billing\\Models\\Invoice');

        $this->assertSame(
            'App\\Modules\\Billing\\Database\\Factories\\InvoiceFactory',
            $factory,
        );
    }

    public function test_default_factory_resolution_still_works(): void
    {
        $this->assertSame(
            'Database\\Factories\\UserFactory',
            Factory::resolveFactoryName('App\\Models\\User'),
        );
    }

    public function test_generators_create_a_complete_module_and_its_classes(): void
    {
        $files = new Filesystem;
        $originalAppPath = app()->path();
        $temporaryPath = sys_get_temp_dir().'/project-manager-modules-'.Str::uuid();

        app()->useAppPath($temporaryPath.'/app');

        try {
            $this->artisan('make:module', [
                'name' => 'Catalog',
                '--plain' => true,
            ])->assertSuccessful();
            $this->artisan('make:module-model', [
                'module' => 'Catalog',
                'name' => 'Product',
                '--factory' => true,
                '--migration' => true,
            ])->assertSuccessful();
            $this->artisan('make:module-request', [
                'module' => 'Catalog',
                'name' => 'StoreProduct',
                '--fetch' => true,
            ])->assertSuccessful();
            $this->artisan('make:module-resource', [
                'module' => 'Catalog',
                'name' => 'Product',
            ])->assertSuccessful();
            $this->artisan('make:module-repository', [
                'module' => 'Catalog',
                'name' => 'Product',
            ])->assertSuccessful();
            $this->artisan('make:module-service', [
                'module' => 'Catalog',
                'name' => 'Product',
            ])->assertSuccessful();

            $modulePath = $temporaryPath.'/app/Modules/Catalog';

            $this->assertDirectoryDoesNotExist($modulePath.'/Commands');
            $this->assertFileExists($modulePath.'/Providers/CatalogServiceProvider.php');
            $this->assertStringContainsString(
                'namespace App\\Modules\\Catalog\\Providers;',
                $files->get($modulePath.'/Providers/CatalogServiceProvider.php'),
            );
            $this->assertFileExists($modulePath.'/Models/Product.php');
            $this->assertFileExists($modulePath.'/Database/Factories/ProductFactory.php');
            $this->assertNotEmpty($files->glob($modulePath.'/Database/Migrations/*_create_products_table.php'));
            $this->assertFileExists($modulePath.'/Requests/StoreProductRequest.php');
            $this->assertFileExists($modulePath.'/Resources/ProductResource.php');
            $this->assertFileExists($modulePath.'/Repositories/ProductRepository.php');
            $this->assertFileExists($modulePath.'/Services/ProductService.php');
        } finally {
            app()->useAppPath($originalAppPath);
            $files->deleteDirectory($temporaryPath);
        }
    }

    public function test_interactive_module_wizard_creates_the_selected_stack(): void
    {
        $files = new Filesystem;
        $originalAppPath = app()->path();
        $temporaryPath = sys_get_temp_dir().'/project-manager-module-wizard-'.Str::uuid();

        app()->useAppPath($temporaryPath.'/app');

        try {
            $this->artisan('make:module')
                ->expectsQuestion('What is the module name?', 'Inventory')
                ->expectsQuestion('What is the primary class/model name?', 'Product')
                ->expectsConfirmation('Create a model?', 'yes')
                ->expectsConfirmation('Create a model factory?', 'yes')
                ->expectsConfirmation('Create a migration?', 'yes')
                ->expectsConfirmation('Create a repository?', 'yes')
                ->expectsConfirmation('Create a service?', 'yes')
                ->expectsConfirmation('Create an API controller?', 'yes')
                ->expectsConfirmation('Create a web controller?', 'yes')
                ->expectsConfirmation('Create a standard request?', 'yes')
                ->expectsConfirmation('Create a fetch request?', 'yes')
                ->expectsConfirmation('Create an API resource?', 'yes')
                ->expectsConfirmation('Create the module now?', 'yes')
                ->assertSuccessful();

            $modulePath = $temporaryPath.'/app/Modules/Inventory';

            $this->assertDirectoryDoesNotExist($modulePath.'/Commands');
            $this->assertFileExists($modulePath.'/Models/Product.php');
            $this->assertFileExists($modulePath.'/Database/Factories/ProductFactory.php');
            $this->assertNotEmpty($files->glob($modulePath.'/Database/Migrations/*_create_products_table.php'));
            $this->assertFileExists($modulePath.'/Repositories/ProductRepository.php');
            $this->assertFileExists($modulePath.'/Services/ProductService.php');
            $this->assertFileExists($modulePath.'/Controllers/Api/ProductController.php');
            $this->assertFileExists($modulePath.'/Controllers/Web/ProductController.php');
            $this->assertFileExists($modulePath.'/Requests/StoreProductRequest.php');
            $this->assertFileExists($modulePath.'/Requests/FetchProductRequest.php');
            $this->assertFileExists($modulePath.'/Resources/ProductResource.php');
        } finally {
            app()->useAppPath($originalAppPath);
            $files->deleteDirectory($temporaryPath);
        }
    }
}
