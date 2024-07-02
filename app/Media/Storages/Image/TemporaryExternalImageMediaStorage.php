<?php declare(strict_types=1);

namespace App\Media\Storages\Image;

use App\Exceptions\UnableToLoadDataException;
use App\Exceptions\ValidationException;
use App\Helpers\EnvironmentHelper;
use App\Media\DataMedium;
use App\Models\Image;
use App\Models\Interfaces\IEntityMedia;
use App\Providers\MediaStorageTypesProvider;
use App\Providers\SettingsProvider;
use App\Services\Bunny\Bunny;
use App\Services\Deadpool\Image as DeadpoolImage;
use App\Services\DI;
use App\Services\Doctrine\EntityManager;
use App\Tasks\ImageStorageConvertTask;
use Nette\NotImplementedException;
use RuntimeException;

class TemporaryExternalImageMediaStorage implements IImageMediaStorage
{
    private const CONFIG_KEY = 'externalUrl';

    /**
     * @var IEntityMedia|Image
     */
    private IEntityMedia|Image $entity;

    /**
     * @param IEntityMedia $entity
     */
    public function __construct(IEntityMedia $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param string $name
     * @param string $extension
     */
    public static function forceDelete(string $name, string $extension): void {}

    /**
     * @return DataMedium
     */
    public function getMedium(): DataMedium
    {
        return DataMedium::fromUrl(
            $this->entity->getStringConfig(self::CONFIG_KEY)
        );
    }

    /**
     * @param DataMedium $media
     * @return $this
     */
    public function store(DataMedium $media): self
    {
        if (! $url = $media->getUrl()) {
            throw new ValidationException(__CLASS__ . ' requires URL address for data medium!');
        }

        $this->entity->addConfig(self::CONFIG_KEY, $url);

        EntityManager::getInstance()->pushToPostFlushStack(function () {
            DI::getInstance()
                ->get(Bunny::class)
                ->add(ImageStorageConvertTask::create($this->entity->getName(), $this->getDefaultStorageType()));
        });

        return $this;
    }

    /**
     * @param IImageMediaStorage $storage
     * @return $this
     */
    public function clone(IImageMediaStorage $storage): IImageMediaStorage
    {
        throw new NotImplementedException(__METHOD__ . ' is not implemented!');
    }

    /**
     * @param string $class
     * @return void
     */
    public function convertTo(string $class): void
    {
        $this->convert(MediaStorageTypesProvider::resolveTypeByClass($class));
    }

    /**
     * @param array $arguments
     * @return void
     */
    public function resize(array $arguments): void
    {
        $this->convert()?->resize($arguments);
    }

    /**
     * @param DeadpoolImage $image
     * @return string
     */
    public function getResizeUrl(DeadpoolImage $image): string
    {
        return $this->convert()?->getResizeUrl($image) ?? '';
    }

    /**
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return $this->convert()?->getDownloadUrl() ?? '';
    }

    /**
     * @param callable|null $callback
     * @return never
     */
    public function download(?callable $callback = null): never
    {
        $this->convert()?->download($callback);
        exit();
    }

    /**
     * @return void
     */
    public function delete(): void {}

    /**
     * @param string|null $storageType
     * @return IImageMediaStorage|null
     */
    private function convert(?string $storageType = null): ?IImageMediaStorage
    {
        $entityManager = EntityManager::getInstance();
        if (! $storageType) {
            $storageType = $this->getDefaultStorageType();
        }

        $class = MediaStorageTypesProvider::resolveClassByType($storageType);

        try {
            $new = new $class($this->entity);
            $new->store($this->getMedium());
            $this->entity->setStorageType($storageType);
            $entityManager->flush();

            return $new;
        } catch (UnableToLoadDataException) {
        } catch (RuntimeException) {
            $entityManager->remove($this->entity);
            $entityManager->flush();
        }

        return null;
    }

    /**
     * @return string
     */
    private function getDefaultStorageType(): string
    {
        return EnvironmentHelper::isDev()
            ? 'LocalImage'
            : SettingsProvider::getInstance()->getString('defaultImagesStorage', 'LocalImage');
    }
}
