<?php declare(strict_types=1);

namespace App\Services;

use App\Media\Storages\Image\LocalImageMediaStorage;
use App\Models\Image;
use App\Models\Repositories\ImageRepository;
use App\Utils\Shell;
use Nette\Utils\FileSystem;

class ImagesService
{
    public const SUPPORTED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const CACHE_DAYS_EXPIRATION = '2';

    /**
     * @var ImageRepository
     */
    private ImageRepository $imageRepository;

    /**
     * @param ImageRepository $imageRepository
     */
    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    /**
     * @return void
     */
    public function flushLocalCache(): void
    {
        if (! file_exists(LocalImageMediaStorage::DIR_IMAGES_CACHE)) {
            return;
        }

        Shell::exec('rm -rf ' . LocalImageMediaStorage::DIR_IMAGES_CACHE);
    }

    /**
     * @return void
     */
    public function clearLocal(): void
    {
        $this->clearLocalStorage();
        $this->clearLocalCache();
    }

    /**
     * @return void
     */
    private function clearLocalStorage(): void
    {
        if (! file_exists(LocalImageMediaStorage::DIR_IMAGES)) {
            return;
        }

        $db = [];
        $this->imageRepository->findAll()->map(static function (Image $image) use (&$db) {
            $db[] = $image->getName();
        });

        $storage = array_diff(scandir(LocalImageMediaStorage::DIR_IMAGES), ['.', '..']);

        foreach (array_diff($storage, $db) as $name) {
            FileSystem::delete(LocalImageMediaStorage::DIR_IMAGES . DS . $name);
        }
    }

    /**
     * @return void
     */
    private function clearLocalCache(): void
    {
        if (! file_exists(LocalImageMediaStorage::DIR_IMAGES_CACHE)) {
            return;
        }

        Shell::exec('find ' . LocalImageMediaStorage::DIR_IMAGES_CACHE . ' -atime +' . self::CACHE_DAYS_EXPIRATION . ' ! -path "*.htaccess*" -delete');
    }
}
