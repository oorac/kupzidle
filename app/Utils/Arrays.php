<?php declare(strict_types=1);

namespace App\Utils;

use Collator;
use Nette\Utils\Arrays as NetteArrays;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

final class Arrays extends NetteArrays
{
    /**
     * @param array $array
     * @param int $flags
     * @return array
     */
    public static function aSort(array $array, int $flags = Collator::SORT_STRING): array
    {
        (new Collator('cs_CZ'))->asort($array, $flags);

        return $array;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function arSort(array $array): array
    {
        return array_reverse(self::aSort($array), true);
    }

    /**
     * @param $needle
     * @param array $haystack
     * @return bool
     */
    public static function in($needle, array $haystack): bool
    {
        return in_array($needle, $haystack, true);
    }

    /**
     * @param array $array
     * @return array
     */
    public static function keys(array $array): array
    {
        return array_keys($array);
    }

    /**
     * @param array $array
     * @return mixed
     */
    public static function random(array $array): mixed
    {
        return $array[array_rand($array)];
    }

    /**
     * @param $value
     * @param $count
     * @return array
     */
    public static function fill($value, $count): array
    {
        $array = [];
        for ($i = 0; $i < $count; $i++) {
            $array[] = $value;
        }

        return $array;
    }

    /**
     * @param array $array
     * @param bool $preserveKeys
     * @return array
     */
    public static function reverse(array $array, bool $preserveKeys = false): array
    {
        return array_reverse($array, $preserveKeys);
    }

    /**
     * @param array $array
     * @return int|float|null
     */
    public static function findNumber(array $array): int|float|null
    {
        foreach ($array as $item) {
            if (is_numeric($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param array $inputs
     * @param string $glue
     * @return array
     */
    public static function flattenKeys(array $inputs, string $glue = '.'): array
    {
        $result = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($inputs));

        foreach ($iterator as $value) {
            $keys = [];
            foreach (range(0, $iterator->getDepth()) as $depth) {
                if ($subIterator = $iterator->getSubIterator($depth)) {
                    $keys[] = $subIterator->key();
                }
            }

            $result[implode($glue, $keys)] = $value;
        }

        return $result;
    }
}
