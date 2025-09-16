<?php

if (!function_exists('d')) {
    /**
     * Dump the given variables.
     *
     * @param mixed ...$vars
     * @return void
     */
    function d(...$vars): void
    {
        foreach ($vars as $var) {
            dump($var);
        }
    }
}

if (!function_exists('dt')) {
    /**
     * Dump the given variables.
     * If the variable is arrayable, it will be converted to an array before dumping.
     *
     * @param mixed ...$vars
     * @return void
     */
    function dt(...$vars): void
    {
        foreach ($vars as $var) {
            if ($var instanceof Illuminate\Contracts\Support\Arrayable) {
                dump($var->toArray());
            } else {
                dump($var);
            }
        }
    }
}

if (!function_exists('ddt')) {
    /**
     * Dump the given variables and terminate the script.
     * It will call the dt function for each variable before dying.
     *
     * @param mixed ...$vars
     * @return void
     */
    function ddt(...$vars): void
    {
        foreach ($vars as $var) {
            dt($var);
        }
        die;
    }
}