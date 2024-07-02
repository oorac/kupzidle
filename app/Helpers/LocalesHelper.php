<?php declare(strict_types=1);

namespace App\Helpers;

final class LocalesHelper
{
    public const DEFAULT = 'cs';

    /**
     * @return string
     */
    public static function getActive(): string
    {
        return self::DEFAULT;
    }

    /**
     * @return string
     */
    public static function getDefault(): string
    {
        return self::DEFAULT;
    }

    /**
     * @return array
     */
    public static function getWhitelist(): array
    {
        return [self::DEFAULT];
    }

    /**
     * @return array
     */
    public static function getAll(): array
    {
        return [self::DEFAULT];
    }
}
