<?php declare(strict_types=1);

namespace App\Utils;

use Nette\Utils\Strings as NetteStrings;

class Strings extends NetteStrings
{
    /**
     * @param $input
     * @return string
     */
    public static function toPlaintext($input): string
    {
        $string = (string) $input;
        $string = html_entity_decode($string);
        $string = htmlspecialchars_decode($string);

        $string = str_replace(PHP_EOL, ' ', $string);
        $string = preg_replace('/<(.*?)>/', ' ', $string);
        $string = str_replace('  ', ' ', $string);

        return trim($string);
    }

    /**
     * @param string $string
     * @return array
     */
    public static function words(string $string): array
    {
        $words = explode(' ', $string);
        $words = preg_replace('/\PL/u', '', $words);

        return array_filter($words);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function removeWhiteSpaces(string $string): string
    {
        return preg_replace('/\s+/', '', $string);
    }

    /**
     * @param string $string
     * @return float
     */
    public static function filterNumbersOnly(string $string): float
    {
        $value = str_replace(',', '.', $string);

        return (float) preg_replace('/(?!^-)[^\d.]/', '', $value);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function formatPhoneNumber(string $string): string
    {
        if (preg_match('/00([1-9])(\d{2})(\d{3})(\d{3})(\d{3})$/', $string, $matches)) {
            return '+' . $matches[1] . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4] . ' ' . $matches[5];
        }

        if (preg_match('/([1-9])(\d{2})(\d{3})(\d{3})(\d{3})$/', $string, $matches)) {
            return '+' . $matches[1] . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4] . ' ' . $matches[5];
        }

        return $string;
    }
}
