<?php

namespace Pfrug\LaravelDebugHelpers;

class SqlHelper
{
    /**
     * Combines SQL and its bindings
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return string
     */
    public static function sqlFromBindings($query, $print = true)
    {
        $percentTemp = '^&^';
        $sql = str_replace(['%', '?'], [$percentTemp, '%s'], $query->toSql());

        $params = collect($query->getBindings())->map(
            function ($binding) {
                return is_numeric($binding) ? $binding : "'{$binding}'";
            }
        )->toArray();

        $str = vsprintf($sql, $params);
        $sql = str_replace($percentTemp, '%', $str);
        if ($print) {
            print($sql);
            die;
        } else {
            return $sql;
        }
    }
}