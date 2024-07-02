<?php declare(strict_types=1);

namespace App\Helpers;

use App\Providers\AgentProvider;
use Nette\Utils\Image as NetteImage;
use Nette\Utils\Image;
use RuntimeException;

final class ImageHelper
{
    /**
     * @param string $mime
     * @return string
     */
    public static function mimeTypeToExt(string $mime): string
    {
        $map = [
            'image/gif' => 'gif',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/x-png' => 'png',
        ];

        if (! isset($map[$mime])) {
            throw new RuntimeException('Undefined mime type `' . $mime . '`');
        }

        return $map[$mime];
    }

    /**
     * @param string $mime
     * @return int
     */
    public static function mimeTypeToImageType(string $mime): int
    {
        $map = [
            'image/gif' => Image::GIF,
            'image/jpeg' => Image::JPEG,
            'image/png' => Image::PNG,
            'image/webp' => Image::WEBP,
            'image/x-png' => Image::PNG,
        ];

        if (! isset($map[$mime])) {
            throw new RuntimeException('Undefined mime type `' . $mime . '`');
        }

        return $map[$mime];
    }

    /**
     * @param int $type
     * @return string
     */
    public static function imageTypeToExtension(int $type): string
    {
        $map = [
            Image::GIF => 'gif',
            Image::JPEG => 'jpg',
            Image::PNG => 'png',
            Image::WEBP => 'webp',
        ];

        if (! isset($map[$type])) {
            throw new RuntimeException('Undefined image type `' . $type . '`');
        }

        return $map[$type];
    }

    /**
     * @param string $extension
     * @return int
     */
    public static function extensionToImageType(string $extension): int
    {
        $map = [
            'gif' => Image::GIF,
            'jpeg' => Image::JPEG,
            'jpg' => Image::JPEG,
            'png' => Image::PNG,
            'webp' => Image::WEBP,
        ];

        if (! isset($map[$extension])) {
            throw new RuntimeException('Undefined image extension `' . $extension . '`');
        }

        return $map[$extension];
    }

    /**
     * @param int $quality 1..100
     * @param int $imageType
     * @return int
     */
    public static function convertQuality(int $quality, int $imageType): int
    {
        $quality = min((int) abs($quality), 100);

        if (in_array($imageType, [NetteImage::PNG, NetteImage::GIF], true)) {
            return (int) ceil($quality / 10);
        }

        return $quality;
    }

    /**
     * @return bool
     */
    public static function clientSupportsWebP(): bool
    {
        if (empty($_SESSION['ClientWebpSupport'])) {
            $agent = AgentProvider::getAgent();
            $browser = $agent->browser();
            $version = $agent->version($browser, $agent::VERSION_TYPE_FLOAT);

            $_SESSION['ClientWebpSupport'] = ($browser === 'Opera Mini')
                || ($browser === 'Edge' && $version >= 18.0)
                || ($browser === 'Firefox' && $version >= 65.0)
                || ($browser === 'Chrome' && $version >= 9.0)
                || ($browser === 'Safari' && $version >= 14.0)
                || ($browser === 'Opera' && $version >= ($agent->isMobile() ? 12.0 : 19.0));
        }

        return $_SESSION['ClientWebpSupport'];
    }
}