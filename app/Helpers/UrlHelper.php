<?php declare(strict_types=1);

namespace App\Helpers;

use App\Memory\RuntimeMemory;
use App\Providers\SettingsProvider;
use App\Services\DI;
use App\Utils\Url;

final class UrlHelper
{
    /**
     * @return string
     */
    public static function getDomain(): string
    {
        return RuntimeMemory::load('UrlHelper', 'domain', static function () {
            if (empty($_SERVER['HTTP_HOST'])) {
                $siteUrl = DI::getInstance()->get(SettingsProvider::class)->getString('siteUrl');

                return Url::parse($siteUrl)->getHost();
            }

            return $_SERVER['HTTP_HOST'];
        });
    }

    /**
     * @return string
     */
    public static function getBaseUrl(): string
    {
        return RuntimeMemory::load('UrlHelper', 'baseUrl', static function () {
            return (! empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . self::getDomain();
        });
    }

    /**
     * @return string
     */
    public static function getFullUrl(): string
    {
        return RuntimeMemory::load('UrlHelper', 'fullUrl', static function () {
            return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        });
    }

    /**
     * @param string $url
     * @param bool $permanent
     * @return never
     */
    public static function redirect(string $url, bool $permanent = false): never
    {
        if (headers_sent() === false) {
            header('Location: ' . $url, true, ($permanent) ? 301 : 302);
        }

        exit();
    }

    /**
     * @param array $parsedUrl
     * @return string
     */
    public static function buildUrl(array $parsedUrl): string
    {
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = $parsedUrl['host'] ?? '';
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user = $parsedUrl['user'] ?? '';
        $pass = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = $parsedUrl['path'] ?? '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        return "{$scheme}{$user}{$pass}{$host}{$port}{$path}{$query}{$fragment}";
    }

    /**
     * @param string $key
     */
    public static function store(string $key): void
    {
        if (empty($_SESSION['UrlHelperStorage'])) {
            $_SESSION['UrlHelperStorage'] = [];
        }
        $_SESSION['UrlHelperStorage'][$key] = self::getFullUrl();
    }

    /**
     * @param string $key
     * @return string|null
     */
    public static function restore(string $key): ?string
    {
        if (empty($_SESSION['UrlHelperStorage'][$key])) {
            return null;
        }

        $url = $_SESSION['UrlHelperStorage'][$key];
        unset($_SESSION['UrlHelperStorage'][$key]);

        return $url;
    }

    /**
     * @param string|null $url
     * @return string
     */
    public static function encode(?string $url = null): string
    {
        $url = $url ?? self::getFullUrl();

        return RuntimeMemory::load('UrlHelper', 'encode-' . $url, static function () use ($url) {
            return CryptoHelper::encode($url, 'URL', true);
        });
    }

    /**
     * @param string $imprint
     * @return string
     */
    public static function decode(string $imprint): string
    {
        return RuntimeMemory::load('UrlHelper', 'decode-' . $imprint, static function () use ($imprint) {
            return CryptoHelper::decode($imprint, 'URL', true);
        });
    }
}
