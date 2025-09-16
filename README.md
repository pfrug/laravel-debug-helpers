# Laravel Debug Helpers

Useful debugging helpers for Laravel development.

## Installation

```bash
composer require pfrug/laravel-debug-helpers
```

## Usage

### Debug Functions

#### `d()`
Dump variables without stopping execution:

```php
$user = User::find(1);
d($user);
d($user->name, $user->email);
```

#### `dt()`
Dump variables, converting Arrayable objects to arrays:

```php
$users = User::all();
dt($users); // Will call toArray() on the collection
```

#### `ddt()`
Dump variables and die:

```php
ddt($query->toArray());
```

### SQL Query Helper

#### `SqlHelper::sqlFromBindings()`
Show the actual SQL query with bindings:

```php
use Pfrug\LaravelDebugHelpers\SqlHelper;

$query = User::where('active', 1)->where('name', 'like', '%john%');

// Print and die
SqlHelper::sqlFromBindings($query);

// Return as string
$sql = SqlHelper::sqlFromBindings($query, false);
echo $sql;
```

## License

MIT