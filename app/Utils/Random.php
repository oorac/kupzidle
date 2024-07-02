<?php declare(strict_types=1);

namespace App\Utils;

use Exception;
use Nette\Utils\Random as LegacyRandom;

class Random
{
    /**
     * @param int $length
     * @param string $charList
     * @return string
     */
	public static function generate(int $length = 10, string $charList = '0-9a-z'): string
	{
	    return LegacyRandom::generate($length, $charList);
	}

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
	public static function int(int $min, int $max): int
    {
        try {
            return random_int($min, $max);
        } catch (Exception) {
            return Arrays::random([$min, $max]);
        }
	}

    /**
     * @param float $min
     * @param float $max
     * @return float
     */
	public static function float(float $min, float $max): float
    {
        return self::int((int) ($min * 10000000), (int) ($max * 10000000)) / 10000000;
	}

    /**
     * @return bool
     */
	public static function bool(): bool
    {
        try {
            return (bool) random_int(0, 1);
        } catch (Exception) {
            return (bool) Arrays::random([0, 1]);
        }
	}

    /**
     * @param array $array
     * @return mixed
     */
	public static function array(array $array): mixed
    {
        return Arrays::random($array);
	}
}
