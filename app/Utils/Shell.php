<?php declare(strict_types=1);

namespace App\Utils;

use App\Memory\FlatFileMemory;
use App\Memory\RuntimeMemory;

class Shell
{
    /**
     * @param string $command
     * @return string
     */
    public static function exec(string $command): string
    {
        return (string) shell_exec($command);
    }

    /**
     * @param string $command
     */
    public static function execToNull(string $command): void
    {
        shell_exec($command . ' > /dev/null');
    }

    /**
     * @param string $command
     */
    public static function execParallelToNull(string $command): void
    {
        shell_exec($command . ' >/dev/null 2>&1 &');
    }

    /**
     * @return string
     */
    public static function php(): string
    {
        $versioned = 'php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        return RuntimeMemory::load('Shell', $versioned, static function () use ($versioned) {
            return FlatFileMemory::load('Shell', $versioned, static function () use ($versioned) {
                return self::commandExist($versioned) ? $versioned : 'php';
            });
        });
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function commandExist(string $name): bool
    {
        return shell_exec('command -v ' . $name) !== null;
    }
}
