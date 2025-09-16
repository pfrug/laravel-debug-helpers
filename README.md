# laravel-debug-helpers

A small set of developer utilities for Laravel. These helpers provide debugging functions that should probably exist in Laravel by default.

## Features

### Dump helpers

#### `d(...$vars)`
Like `dump()`, but does **not** stop execution.

#### `dt(...$vars)`
Like `dump()`, but if the variable is arrayable (e.g. Eloquent models), it converts it to array before dumping.

#### `ddt(...$vars)`
Same as `dt()`, but dies after dumping.

You can use these in place of `dump()` and `dd()` when working with Eloquent models or arrays.

---

### DbHelper

#### `DbHelper::sqlWithBindings($query, $print = true)`

Combines `toSql()` and `getBindings()` on a query builder or Eloquent builder to output the full SQL string with all bindings inlined.

This is useful for debugging complex queries.

```php
use LaravelDebugHelpers\DbHelper;

$query = User::where('email', 'test@example.com')
             ->where('active', 1);

DbHelper::sqlWithBindings($query);
```

Output:

```sql
select * from `users` where `email` = 'test@example.com' and `active` = 1
```

If `$print` is `false`, it returns the SQL string instead of printing and stopping.

---

## Installation

Install via composer:

```bash
composer require your-vendor/laravel-debug-helpers
```

If package auto-discovery is disabled, register the service provider manually in `config/app.php`:

```php
'providers' => [
    LaravelDebugHelpers\DevUtilsServiceProvider::class,
],
```

---

## Disclaimer

These utilities are intended for development only. Do not leave `ddt()` or raw SQL dump calls in production environments.
