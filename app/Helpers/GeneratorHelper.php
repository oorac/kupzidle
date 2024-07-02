<?php declare(strict_types=1);

namespace App\Helpers;

use Exception;
use Nette\Utils\Random;

final class GeneratorHelper
{
    /**
     * @return string
     */
    public static function generateTimeID(): string
    {
        return base_convert((string) hrtime(true), 10, 36);
    }

    /**
     * @return string
     */
    public static function generateName(): string
    {
        $chunks = explode('.', (string) microtime(true));
        $time = $chunks[0];
        $micro = $chunks[1] ?? '0000';
        $micro = str_pad($micro ?: '0000', 4, '0', STR_PAD_RIGHT);

        return strrev(
            base_convert($time, 10, 36)
            . base_convert($micro, 10, 36)
            . Random::generate(6)
        );
    }

    /**
     * @return string
     */
    public static function generateUniqueHash(): string
    {
        $time = str_replace('.', '', (string) (microtime(true) / 2));

        return base_convert($time, 10, 36);
    }

    /**
     * @return string
     */
    public static function generateSHA256Hash(): string
    {
        try {
            $random = random_bytes(128);
        } catch (Exception $e) {
            $random = Random::generate(128);
        }

        return hash('sha256', uniqid('random', true) . $random);
    }
}
