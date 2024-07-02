<?php declare(strict_types=1);

namespace App\Helpers;

final class FilesHelper
{
    /**
     * @param array $files
     * @return string
     */
    public static function getModificationsHash(array $files): string
    {
        $hash = '';
        foreach ($files as $path) {
            if (file_exists($path)) {
                $hash .= filemtime($path);
            }
        }

        return md5($hash);
    }
}