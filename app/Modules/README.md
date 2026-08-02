# Application modules

Create a module:

```bash
php artisan make:module Billing
```

Create classes inside it:

```bash
php artisan make:module-model Billing Invoice --factory --migration
php artisan make:module-request Billing StoreInvoice
php artisan make:module-request Billing FetchInvoice --fetch
php artisan make:module-resource Billing Invoice
php artisan make:module-repository Billing Invoice
php artisan make:module-service Billing Invoice
```

All class generators accept `--force`. Repository and service dependencies can be
overridden with `--model` and `--repository`, respectively. Nested class names such
as `Admin/Invoice` are supported.

Modules are discovered automatically. Their providers, commands, migrations, and
routes do not need to be registered manually. API and web routes receive their
matching middleware groups. Dashboard routes use the prefix and middleware in
`config/project.php` under `project.routes.dashboard`.

Module tests belong in each module's `Tests/Feature` or `Tests/Unit` directory and
run in the PHPUnit `Modules` testsuite.
