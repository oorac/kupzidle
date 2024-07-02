<?php declare(strict_types=1);

namespace App\Helpers;

use Exception;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;

class CryptoHelper
{
    private const KEY_PATH = DIR_STORAGE . DS . 'crypto-helper.key';

    /**
     * @var array
     */
    private static array $characters = [
        ' ', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', '0', '1', '2', '3', '4', '5',
        '6', '7', '8', '9', ':', ';', '<', '=', '>', '?', '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
        'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']', '^', '_', '`', 'a',
        'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w',
        'x', 'y', 'z', '{', '|', '}', '~',
    ];

    /**
     * @param string $string
     * @param string $key
     * @param bool $base64
     * @return string
     */
    public static function encode(string $string, string $key = '', bool $base64 = false): string
    {
        $output = static::processEncode($string, $key, self::$characters);
        if ($base64) {
            $output = base64_encode($output);
        }

        return $output;
    }

    /**
     * @param string $string
     * @param string $key
     * @param bool $base64
     * @return string
     */
    public static function decode(string $string, string $key = '', bool $base64 = false): string
    {
        if ($base64) {
            $string = base64_decode($string);
        }

        return static::processDecode($string, $key, self::$characters);
    }

    /**
     * @param string $string
     * @param string $key
     * @param array $characters
     * @return string
     */
    private static function processEncode(string $string, string $key, array $characters): string
    {
        $output = [];
        $flipped = array_flip($characters);
        $password = mb_str_split(static::regenerateKey($key));

        $passwordLength = count($password);
        $charactersLength = count($characters);

        foreach (mb_str_split($string) as $i => $char) {
            $volume = ($flipped[$char] + $flipped[$password[$i % $passwordLength]]) % $charactersLength;
            $output[] = $characters[$volume];
        }

        return implode('', $output);
    }

    /**
     * @param string $string
     * @param string $key
     * @param array $characters
     * @return string
     */
    private static function processDecode(string $string, string $key, array $characters): string
    {
        $output = [];
        $flipped = array_flip($characters);
        $password = mb_str_split(static::regenerateKey($key));

        $passwordLength = count($password);
        $charactersLength = count($characters);

        foreach (mb_str_split($string) as $i => $char) {
            $volume = $flipped[$char] - $flipped[$password[$i % $passwordLength]];
            $volume = $volume < 0 ? $charactersLength + $volume : $volume;
            $output[] = $characters[$volume];
        }

        return implode('', $output);
    }

    /**
     * @param string $key
     * @return string
     */
    private static function regenerateKey(string $key): string
    {
        $parts = str_split($key, 3);
        $regen = '';

        foreach ($parts as $i => $part) {
            $regen .= ($i ? 0x79C * $i : null) . $part;
        }

        return md5(self::loadLocalKey() . 0x89B . $regen);
    }

    /**
     * @return string
     */
    private static function loadLocalKey(): string
    {
        try {
            $key = FileSystem::read(self::KEY_PATH);
        } catch (Exception) {
            $key = null;
        }

        if (! $key) {
            $key = Random::generate((int) Random::generate(2, '0-9'));
            FileSystem::write(self::KEY_PATH, $key);
        }

        return $key;
    }
}
