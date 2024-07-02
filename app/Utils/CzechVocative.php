<?php declare(strict_types=1);

namespace App\Utils;

use RuntimeException;

class CzechVocative
{
    public const MALE = 'male';
    public const FEMALE = 'female';

    private const MALE_MAP = [
        'p' => 'pe',
        'b' => 'be',
        'm' => 'me',
        'n' => 'ne',
        'l' => 'le',
        'd' => 'de',
        'v' => 've',
        't' => 'te',
        'f' => 'fe',
        'j' => 'ji',
        'š' => 'ši',
        'ž' => 'ži',
        'ř' => 'ři',
        's' => 'si',
        'a' => 'o',
        'or' => 'ore',
        'er' => 'ere',
        'ur' => 'ure',
        'ar' => 'are',
        'ir' => 'íre',
        'ír' => 'íre',
        'yr' => 'yre',
        'r' => 'ře',
        'ej' => 'eji',
        'ek' => 'ku',
        'něk' => 'ňku',
        'ch' => 'chu',
        'k' => 'ku',
        'x' => 'xi',
        'c' => 'ci',
        'g' => 'gu',
    ];

    private const FEMALE_MAP = [
        'a' => 'o',
    ];

    /**
     * @param string $name
     * @param string $gender
     * @return string
     */
    public static function convert(string $name, string $gender = self::MALE): string
    {
        return match ($gender) {
            self::MALE => self::convertMale($name),
            self::FEMALE => self::convertFemale($name),
            default => throw new RuntimeException('There are only two genders and you know it, you `' . $gender . '`'),
        };
    }

    /**
     * @param string $name
     * @return string
     */
    public static function convertMale(string $name): string
    {
        foreach (self::MALE_MAP as $search => $replace) {
            if (str_ends_with($name, $search)) {
                return preg_replace('~' . $search . '$~', $replace, $name);
            }
        }

        return $name;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function convertFemale(string $name): string
    {
        foreach (self::FEMALE_MAP as $search => $replace) {
            if (str_ends_with($name, $search)) {
                return preg_replace('~' . $search . '$~', $replace, $name);
            }
        }

        return $name;
    }
}
