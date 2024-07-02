<?php declare(strict_types=1);

namespace App\Helpers;

use Tracy\Debugger;

final class EnvironmentHelper
{
    private const PRODUCTION_HOST = 'digilabs';
    private const PRODUCTION_IP = '31.31.239.206';

    /**
     * @var bool|null
     */
    private static ?bool $isProduction = null;

    /**
     * @return bool
     */
    public static function isDev(): bool
    {
        return ! self::isProduction();
    }

    /**
     * @return bool
     */
    public static function isProduction(): bool
    {
        if (self::$isProduction === null) {
            self::$isProduction = self::detectProduction();
        }

        return self::$isProduction;
    }

    /**
     * @return bool
     */
    public static function detectProduction(): bool
    {
        if (Debugger::$productionMode === true) {
            return true;
        }

        if (str_contains(__DIR__, DS . 'vagrant' . DS)) {
            return false;
        }

        $host = gethostname();
        if ($host === self::PRODUCTION_HOST) {
            return true;
        }

        $ip = gethostbyname($host);

        return $ip === self::PRODUCTION_IP;
    }
}