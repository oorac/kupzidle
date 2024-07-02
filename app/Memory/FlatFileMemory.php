<?php declare(strict_types=1);

namespace App\Memory;

use App\Memory\FlatFileMemory\Journal;
use DateTime;
use Exception;
use Nette\Utils\FileSystem;

final class FlatFileMemory
{
    private const FOLDER = DIR_TEMP . DS . 'cache' . DS . '_FlatFileMemory';

    /**
     * @var Journal|null
     */
    private static ?Journal $journal = null;

    private function __construct() {}

    /**
     * @param string $namespace
     * @param string $key
     * @return mixed
     */
    public static function get(string $namespace, string $key): mixed
    {
        $path = self::getCachePath($namespace, $key);
        if (! file_exists($path)) {
            return null;
        }

        return self::getJournal()->load($namespace, $key)
            ? require $path
            : null;
    }

    /**
     * @param string $namespace
     * @param string $key
     * @param callable $callback
     * @param string|null $expiration
     * @param array $tags
     * @return mixed
     */
    public static function load(
        string $namespace,
        string $key,
        callable $callback,
        ?string $expiration = null,
        array $tags = []
    ): mixed {
        $path = self::getCachePath($namespace, $key);
        if (! file_exists($path)) {
            return self::refresh($namespace, $key, $callback, $expiration, $tags);
        }

        $info = self::getJournal()->load($namespace, $key);
        if (! $info) {
            return self::refresh($namespace, $key, $callback, $expiration, $tags);
        }

        return require $path;
    }

    /**
     * @param string $namespace
     * @param string $key
     * @param $data
     * @param string|null $expiration
     * @param array $tags
     * @return mixed
     */
    public static function store(string $namespace, string $key, $data, ?string $expiration = null, array $tags = []): mixed
    {
        FileSystem::createDir(self::FOLDER . DS . $namespace . DS);

        file_put_contents('nette.safe://' . self::FOLDER . DS . $namespace . DS . $key . '.php', self::exportData($data));

        try {
            $expires = $expiration ? (new DateTime('+' . $expiration))->getTimestamp() : null;
        } catch (Exception) {
            $expires = null;
        }

        self::getJournal()->write($namespace, $key, $expires, $tags);

        return $data;
    }

    /**
     * @param string|null $namespace
     * @param string|null $key
     * @param array $keys
     */
    public static function clean(?string $namespace, ?string $key = null, array $keys = []): void
    {
        foreach (self::getJournal()->clean($namespace, $key, $keys) as $item) {
            $path = self::getCachePath($item->namespace, $item->key);
            FileSystem::delete($path);
        }
    }

    /**
     * @param $data
     * @return string
     */
    private static function exportData($data): string
    {
        return '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($data, true) . ';';
    }

    /**
     * @param string $namespace
     * @param string $key
     * @param callable $callback
     * @param string|null $expiration
     * @param array $tags
     * @return mixed
     */
    private static function refresh(
        string $namespace,
        string $key,
        callable $callback,
        ?string $expiration = null,
        array $tags = []
    ): mixed {
        $data = $callback();
        if ($data === null) {
            self::clean($namespace, $key);
        } else {
            self::store($namespace, $key, $data, $expiration, $tags);
        }

        return $data;
    }

    /**
     * @return Journal
     */
    private static function getJournal(): Journal
    {
        if (empty(self::$journal)) {
            self::$journal = new Journal(self::FOLDER);
        }

        return self::$journal;
    }

    /**
     * @param string $namespace
     * @param string|null $key
     * @return string
     */
    private static function getCachePath(string $namespace, ?string $key = null): string
    {
        if ($key === null) {
            return self::FOLDER . DS . $namespace . DS;
        }

        return self::FOLDER . DS . $namespace . DS . $key . '.php';
    }
}
