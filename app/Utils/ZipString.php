<?php declare(strict_types=1);

namespace App\Utils;

use App\Helpers\GeneratorHelper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

final class ZipString
{
    /**
     * @var string
     */
    private string $path;

    /**
     * @var ?RecursiveIteratorIterator
     */
    private ?RecursiveIteratorIterator $files = null;

    /**
     * @param string $encoded
     */
    public function __construct(private readonly string $encoded)
    {
        $this->path = DIR_TEMP_CACHE . DS . 'ZIPString' . DS . GeneratorHelper::generateUniqueHash();
    }

    /**
     * @return RecursiveIteratorIterator
     */
    public function getContent(): RecursiveIteratorIterator
    {
        if ($this->files === null) {
            FileSystem::write($this->path . DS . 'file.zip', $this->encoded);
            register_shutdown_function(function () {
                FileSystem::delete($this->path);
            });

            $zip = new ZipArchive();
            if (! $zip->open($this->path . DS . 'file.zip')) {
                throw new RuntimeException('Failed to open zip file `' . $this->path . DS . 'file.zip' . '`');
            }

            $zip->extractTo($this->path . DS . 'files' . DS);
            $zip->close();

            $this->files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->path . DS . 'files' . DS)
            );
        }

        return $this->files;
    }
}
