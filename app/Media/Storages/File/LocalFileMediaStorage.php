<?php declare(strict_types=1);

namespace App\Media\Storages\File;

use App\Exceptions\ValidationException;
use App\Helpers\FileHelper;
use App\Helpers\UrlHelper;
use App\Media\DataMedium;
use App\Models\File;
use App\Models\Interfaces\IEntityMedia;
use App\Providers\MediaStorageTypesProvider;
use App\Services\DI;
use App\Services\Doctrine\EntityManager;
use App\Tasks\FileStorageDeleteMediaTask;
use App\Utils\FileSystem;
use Nette\Utils\JsonException;

class LocalFileMediaStorage implements IFileMediaStorage
{
    public const DIR_FILES = DIR_WWW . DS . 'media' . DS . 'files';

    /**
     * @var File
     */
    private File $entity;

    /**
     * @param IEntityMedia $entity
     */
    public function __construct(IEntityMedia $entity)
    {
        if (! $entity instanceof File) {
            throw new ValidationException('Incorrect type');
        }

        $this->entity = $entity;
    }

    /**
     * @return DataMedium
     */
    public function getMedium(): DataMedium
    {
        return DataMedium::fromPath(self::DIR_FILES . DS . $this->entity->getName());
    }

    /**
     * @param DataMedium $media
     * @return $this
     */
    public function store(DataMedium $media): self
    {
        $this->entity->preStore($media);
        FileSystem::writeSafe(self::DIR_FILES . DS . $this->entity->getName(), $media->getData());
        $this->entity->postStore($media);

        return $this;
    }

    /**
     * @param IFileMediaStorage $storage
     * @return $this
     */
    public function clone(IFileMediaStorage $storage): self
    {
        return $this->store($storage->getMedium());
    }

    /**
     * @param string $class
     * @return void
     */
    public function convertTo(string $class): void
    {
        if ($class === self::class)  {
            return;
        }

        if (! is_subclass_of($class, IFileMediaStorage::class, true)) {
            throw new ValidationException(sprintf("Storage type must be subclass of `%s`, `%s` given.", IFileMediaStorage::class, $class));
        }

        /** @var IFileMediaStorage $new */
        $new = new $class($this->entity);
        $new->store($this->getMedium());

        $this->entity->setStorageType(MediaStorageTypesProvider::resolveTypeByClass($class));

//        EntityManager::getInstance()->pushToPostFlushStack(function () use ($class) {
//            DI::getInstance()
//                ->get(Bunny::class)
//                ->filterQueue(FileStorageDeleteMediaTask::class, [
//                    $this->entity->getName(),
//                    $this->entity->getExtension(),
//                    MediaStorageTypesProvider::resolveTypeByClass($class)
//                ]);
//
//            $this->delete();
//        });
    }

    /**
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return UrlHelper::getBaseUrl() . '/media/files/download/' . $this->entity->getName();
    }

    /**
     * @param callable|null $callback
     * @return never
     */
    public function download(?callable $callback = null): never
    {
        FileHelper::invokeOpen(self::DIR_FILES . DS . $this->entity->getName(), [
            'mime' => $this->entity->getType(),
            'filename' => $this->entity->getFilename(),
            'description' => $this->entity->getTitle(),
        ], $callback);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function delete(): void
    {
        DI::getInstance()
            ->get(Bunny::class)
            ->add(
                FileStorageDeleteMediaTask::create(
                    $this->entity->getName(),
                    $this->entity->getExtension(),
                    MediaStorageTypesProvider::resolveTypeByClass(self::class)
                )->moveRunSince('+1 day')
            );
    }

    /**
     * @param string $name
     * @param string $extension
     */
    public static function forceDelete(string $name, string $extension): void
    {
        if (FileSystem::exists(self::DIR_FILES . DS . $name)) {
            FileSystem::delete(self::DIR_FILES . DS . $name);
        }
    }
}
