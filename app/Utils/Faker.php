<?php declare(strict_types=1);

namespace App\Utils;

use DateTimeImmutable;

final class Faker
{
    private const VOWELS = [
        'a', 'e', 'i', 'y', 'o', 'u'
    ];

    private const CONSONANTS = [
        'b', 'c', 'd', 'f', 'h', 'j',
        'k', 'l', 'm', 'n', 'p', 'r',
        's', 't', 'v',
    ];

    private const BAD_CONSONANTS = [
        'g', 'q', 'w', 'x', 'z'
    ];

    private const VOWELS_SET = [
        self::VOWELS,
        self::VOWELS,
        self::VOWELS,
        self::VOWELS,
        self::VOWELS,
        self::VOWELS,
        self::CONSONANTS,
        self::BAD_CONSONANTS,
    ];

    private const CONSONANTS_SET = [
        self::CONSONANTS,
        self::CONSONANTS,
        self::CONSONANTS,
        self::CONSONANTS,
        self::CONSONANTS,
        self::CONSONANTS,
        self::VOWELS,
        self::BAD_CONSONANTS,
    ];

    /**
     * @return bool
     */
    public static function boolean(): bool
    {
        return (bool) Random::int(0, 1);
    }

    /**
     * @param int $minDays
     * @param int $maxDays
     * @return DateTimeImmutable
     */
    public static function datePast(int $minDays = 1, int $maxDays = 9999): DateTimeImmutable
    {
        return (new DateTimeImmutable())->modify('-' . Random::int($minDays, $maxDays) . ' days');
    }

    /**
     * @param int $minDays
     * @param int $maxDays
     * @return DateTimeImmutable
     */
    public static function dateFuture(int $minDays = 1, int $maxDays = 9999): DateTimeImmutable
    {
        return (new DateTimeImmutable())->modify('+' . Random::int($minDays, $maxDays) . ' days');
    }

    /**
     * @param array $protocols
     * @return string
     */
    public static function domain(array $protocols = []): string
    {
        $protocol = Arrays::random($protocols);

        return ($protocol ? $protocol . '://' : '') . self::word() . '.fake';
    }

    /**
     * @return string
     */
    public static function email(): string
    {
        return self::word() . '@' . self::word() . '.fake';
    }

    /**
     * @param array $prefix
     * @return string
     */
    public static function phone(array $prefix = []): string
    {
        return Arrays::random($prefix) . Random::int(100, 999) . Random::int(100, 999) . Random::int(100, 999);
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function integer(int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        return Random::int($min, $max);
    }

    /**
     * @param int $min
     * @param int $max
     * @return float
     */
    public static function float(int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): float
    {
        return self::integer($min, $max) / Random::int(2, 200);
    }

    /**
     * @param int $min
     * @param int $max
     * @return string
     */
    public static function number(int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): string
    {
        return (string) self::integer($min, $max);
    }

    /**
     * @param int $min
     * @param int $max
     * @return string
     */
    public static function article(int $min = 1, int $max = 12): string
    {
        $paragraphs = [];
        for ($i = 0, $size = Random::int($min, $max); $i < $size; $i++) {
            $paragraphs[] = self::paragraph();
        }

        return implode(PHP_EOL, $paragraphs);
    }

    /**
     * @param int $min
     * @param int $max
     * @return string
     */
    public static function paragraph(int $min = 1, int $max = 12): string
    {
        $sentences = [];
        for ($i = 0, $size = Random::int($min, $max); $i < $size; $i++) {
            $sentences[] = self::sentence();
        }

        return implode('. ', $sentences) . '.';
    }

    /**
     * @param int $min
     * @param int $max
     * @return string
     */
    public static function sentence(int $min = 1, int $max = 12): string
    {
        return ucfirst(implode(' ', self::words($min, $max)));
    }

    /**
     * @param int $min
     * @param int $max
     * @return array
     */
    public static function words(int $min = 1, int $max = 12): array
    {
        $words = [];
        for ($i = 0, $size = Random::int($min, $max); $i < $size; $i++) {
            $words[] = self::word();
        }

        return $words;
    }

    /**
     * @param int $min
     * @param int $max
     * @return string
     */
    public static function name(int $min = 1, int $max = 12): string
    {
        return ucfirst(self::word($min, $max));
    }

    /**
     * @param int $min
     * @param int $max
     * @return string
     */
    public static function word(int $min = 1, int $max = 12): string
    {
        $word = '';
        $char = null;
        $method = Arrays::random(['VOWELS', 'CONSONANTS']);

        for ($i = 0, $size = Random::int($min, $max); $i < $size; $i++) {
            do {
                $prev = $char;
                if ($method === 'VOWELS') {
                    $char = Arrays::random(Arrays::random(self::VOWELS_SET));
                    $method = 'CONSONANTS';
                } else {
                    $char = Arrays::random(Arrays::random(self::CONSONANTS_SET));
                    $method = 'VOWELS';
                }
            } while ($char === $prev);
            $word .= $char;
        }

        return $word;
    }
}
