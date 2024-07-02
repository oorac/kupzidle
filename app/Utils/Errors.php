<?php declare(strict_types=1);

namespace App\Utils;

use Closure;

final class Errors
{
    /**
     * @var bool
     */
    private static bool $handled = false;

    /**
     * @param Closure $callback
     * @param Closure $handler
     * @return mixed
     */
    public static function handle(Closure $callback, Closure $handler): mixed
    {
        set_error_handler(static function (int $number, string $string, string $file, int $line) use ($handler) {
            self::reset();
            $handler($number, $string, $file, $line);
        });

        self::$handled = true;

        $result = $callback();
        self::reset();

        return $result;
    }

    /**
     * @return void
     */
    private static function reset(): void
    {
        if (self::$handled) {
            restore_error_handler();
        }

        self::$handled = false;
    }
}
