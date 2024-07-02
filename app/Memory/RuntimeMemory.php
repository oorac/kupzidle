<?php declare(strict_types=1);

namespace App\Memory;

final class RuntimeMemory
{
    /**
     * @var array
     */
    private static array $memory = [];

    private function __construct() {}

    /**
     * @param string $namespace
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public static function load(string $namespace, string $key, callable $callback): mixed
    {
        if (! empty(self::$memory[$namespace][$key])) {
            return self::$memory[$namespace][$key];
        }

        self::$memory[$namespace][$key] = $callback();

        return self::$memory[$namespace][$key];
    }
}
