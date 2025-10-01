<?php

namespace Pfrug\LaravelDebugHelpers;

use Illuminate\Database\Eloquent\Builder;

class SqlHelper
{
    /**
     * Combines SQL and its bindings, and optionally formats it.
     */
    public static function sqlFromBindings(Builder $query, bool $print = true, bool $format = true): string
    {
        $percentTemp = '^&^';
        $sql = str_replace(['%', '?'], [$percentTemp, '%s'], $query->toSql());

        $params = collect($query->getBindings())->map(
            fn($binding) => is_numeric($binding) ? $binding : "'{$binding}'"
        )->toArray();

        $str = vsprintf($sql, $params);
        $sql = str_replace($percentTemp, '%', $str);

        if ($format) {
            $sql = SqlFormatter::format($sql);
        }

        if ($print) {
            if (php_sapi_name() === 'cli') {
                echo $sql . PHP_EOL;
            } else {
                echo "<pre>" . htmlspecialchars($sql) . "</pre>";
            }
        }

        return $sql;
    }

}
