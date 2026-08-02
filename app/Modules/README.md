# Application modules

Create a module interactively. The wizard asks for the primary class name and
which model, persistence, HTTP, and resource classes to generate before writing
the module:

```bash
php artisan make:module
```

Generate the complete stack without questions:

```bash
php artisan make:module Billing --entity=Invoice --all
```

Use `--plain` to create only the directory, provider, and route structure. Each
component also has an individual option for scripted module generation.

Create classes inside it:

```bash
php artisan make:module-model Billing Invoice --factory --migration
php artisan make:module-controller Billing Invoice --api
php artisan make:module-controller Billing Invoice --web
php artisan make:module-request Billing StoreInvoice
php artisan make:module-request Billing FetchInvoice --fetch
php artisan make:module-resource Billing Invoice
php artisan make:module-repository Billing Invoice
php artisan make:module-service Billing Invoice
```

All class generators accept `--force`. Repository and service dependencies can be
overridden with `--model` and `--repository`, respectively. Nested class names such
as `Admin/Invoice` are supported.

Modules are discovered automatically. Their providers, migrations, and routes do
not need to be registered manually. Artisan commands are a Core-only concern and
belong in `Modules/Core/Commands`; generated modules do not include a `Commands`
directory. API and web routes receive their matching middleware groups. Dashboard
routes use the prefix and middleware in `config/project.php` under
`project.routes.dashboard`.

Module tests belong in each module's `Tests/Feature` or `Tests/Unit` directory and
run in the PHPUnit `Modules` testsuite.
