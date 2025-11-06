<?php
// app/Core/Config.php
// Zentrale Stelle, um ENV-Werte typisiert abzurufen und Defaults zu setzen.

declare(strict_types=1);

namespace App\Core;

final class Config
{
    public static function get(string $key, $default = null)
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $v = getenv($key);
        if ($v === false) return $default;
        return in_array(strtolower($v), ['1','true','yes','on'], true);
    }
}