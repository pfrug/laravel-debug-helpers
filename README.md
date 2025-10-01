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

### SqlHelper

#### `SqlHelper::sqlFromBindings($query, $print = true)`

Combines `toSql()` and `getBindings()` on a query builder or Eloquent builder to output the full SQL string with all bindings inlined.

This is useful for debugging complex queries.

```php
use LaravelDebugHelpers\SqlHelper;

// Complex query with joins and subqueries
$query = User::join('profiles', 'users.id', '=', 'profiles.user_id')
            ->leftJoin('organizations', 'users.organization_id', '=', 'organizations.id')
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['admin', 'moderator']);
            })
            ->where('users.status', 'active')
            ->where('users.email_verified_at', '!=', null)
            ->where(function($q) {
                $q->where('profiles.is_public', 1)
                  ->orWhere('users.account_type', 'premium');
            })
            ->where('organizations.country', 'US')
            ->where('users.created_at', '>=', now()->subMonths(6))
            ->select('users.id', 'users.name', 'users.email', 'profiles.bio', 'organizations.name as org_name')
            ->orderBy('users.created_at', 'desc')
            ->limit(25);

SqlHelper::sqlFromBindings($query);
```

Output:

```sql
SELECT
    `users`.`id`,
    `users`.`name`,
    `users`.`email`,
    `profiles`.`bio`,
    `organizations`.`name` AS `org_name`
FROM `users`
    INNER JOIN `profiles` ON `users`.`id` = `profiles`.`user_id`
    LEFT JOIN `organizations` ON `users`.`organization_id` = `organizations`.`id`
    WHERE EXISTS (
        SELECT
            *
        FROM `roles`
            INNER JOIN `user_roles` ON `roles`.`id` = `user_roles`.`role_id`
        WHERE `users`.`id` = `user_roles`.`user_id`
            AND `name` IN ('admin', 'moderator')
    )
    AND `users`.`status` = 'active'
    AND `users`.`email_verified_at` IS NOT NULL
    AND (
        `profiles`.`is_public` = 1
        OR
        `users`.`account_type` = 'premium'
    )
    AND `organizations`.`country` = 'US'
    AND `users`.`created_at` >= '2025-04-01 14:26:52'
ORDER BY `users`.`created_at` desc
```

If `$print` is `false`, it returns the SQL string instead of printing and stopping.

---

## Installation

Install via composer:

```bash
composer require pfrug/laravel-debug-helpers
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
