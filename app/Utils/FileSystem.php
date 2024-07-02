<?php declare(strict_types=1);

namespace App\Utils;

use Nette\Utils\FileSystem as LegacyFileSystem;

class FileSystem
{
    /**
     * @param string $path
     * @return string
     */
    public static function read(string $path): string
    {
        return LegacyFileSystem::read($path);
    }
    /**
     * @param string $path
     * @return string
     */
    public static function readSafe(string $path): string
    {
        return LegacyFileSystem::read('nette.safe://' . $path);
    }

    /**
     * @param string $file
     * @param string $content
     * @param int|null $mode
     */
    public static function write(string $file, string $content, ?int $mode = 0666): void
    {
        LegacyFileSystem::write($file, $content, $mode);
    }

    /**
     * @param string $file
     * @param int|null $mode
     */
    public static function touch(string $file, ?int $mode = 0666): void
    {
        LegacyFileSystem::write($file, '', $mode);
    }

    /**
     * @param string $file
     * @param string $content
     */
    public static function writeSafe(string $file, string $content): void
    {
        LegacyFileSystem::createDir(dirname($file));
        file_put_contents('nette.safe://' . $file, $content);
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * @param string $path
     */
    public static function delete(string $path): void
    {
        LegacyFileSystem::delete($path);
    }

    /**
     * @param string $origin
     * @param string $target
     * @param bool $overwrite
     */
    public static function move(string $origin, string $target, bool $overwrite = true): void
    {
        self::rename($origin, $target, $overwrite);
    }

    /**
     * @param string $origin
     * @param string $target
     * @param bool $overwrite
     */
    public static function copy(string $origin, string $target, bool $overwrite = true): void
    {
        LegacyFileSystem::copy($origin, $target, $overwrite);
    }

    /**
     * @param string $origin
     * @param string $target
     * @param bool $overwrite
     */
    public static function rename(string $origin, string $target, bool $overwrite = true): void
    {
        LegacyFileSystem::rename($origin, $target, $overwrite);
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function isDir(string $path): bool
    {
        return file_exists($path) && is_dir($path);
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function isFile(string $path): bool
    {
        return file_exists($path) && is_file($path);
    }

    /**
     * @param string $dir
     * @param int $mode
     */
	public static function createDir(string $dir, int $mode = 0777): void
	{
	    LegacyFileSystem::createDir($dir, $mode);
	}

    /**
     * @param string $dir
     * @param int $mode
     */
	public static function mkdir(string $dir, int $mode = 0777): void
	{
	    LegacyFileSystem::createDir($dir, $mode);
	}

    /**.
     * @param string $path
     * @param bool $filterDottedFiles
     * @return array
     */
    public static function scanDir(string $path, bool $filterDottedFiles = true): array
    {
        $files = [];
        if (! self::isDir($path)) {
            return $files;
        }

        foreach (scandir($path) as $file) {
            if ($filterDottedFiles && str_starts_with($file, '.')) {
                continue;
            }

            $files[$path . DS . $file] = $file;
        }

        return $files;
    }

    /**
     * @param string $path
     * @return array
     */
    public static function scanDirRecursive(string $path): array
    {
        $files = [];
        if (! self::isDir($path)) {
            return $files;
        }

        foreach (scandir($path) as $file) {
            if (str_starts_with($file, '.')) {
                continue;
            }

            if (is_dir($path . DS . $file)) {
                foreach (self::scanDirRecursive($path . DS . $file) as $item) {
                    $files[$path . DS . $file . DS . $item] = $item;
                }
                continue;
            }

            $files[$path . DS . $file] = $file;
        }

        return $files;
    }

    /**
     * @param string $path
     * @return mixed|null
     */
    public static function includeSafe(string $path): mixed
    {
        $path = 'nette.safe://' . $path;

        if (! file_exists($path)) {
            return null;
        }

        return @ include $path;
    }

    /**
     * @param string $pattern
     * @return array
     */
    public static function find(string $pattern): array
    {
        return glob($pattern);
    }
}
