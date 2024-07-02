<?php declare(strict_types=1);

namespace App\Utils;

class RandomProfiles
{
    public const PATH = DIR_WWW . DS . 'assets' . DS . 'random-profiles';

    /**
     * @return string
     */
    public static function getPath(): string
    {
        return Arrays::random(Arrays::keys(FileSystem::scanDir(self::PATH)));
    }
}
