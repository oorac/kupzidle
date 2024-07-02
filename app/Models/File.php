<?php declare(strict_types=1);

namespace App\Models;

use App\Helpers\GeneratorHelper;
use App\Media\DataMedium;
use App\Media\Storages\File\IFileMediaStorage;
use App\Models\Attributes\Entity;
use App\Models\Attributes\EntityCreatedOn;
use App\Models\Attributes\EntityID;
use App\Models\Interfaces\IEntity;
use App\Models\Interfaces\IEntityMedia;
use App\Providers\MediaStorageTypesProvider;
use App\Services\Doctrine\EntityManager;
use App\Utils\Strings;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\App\Models\Repositories\FileRepository")
 * @ORM\Table(name="file")
 * @ORM\HasLifecycleCallbacks
 */
class File implements IEntity, IEntityMedia
{
    use Entity;
    use EntityID;
    use EntityCreatedOn;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected string $title = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected string $name = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     */
    protected string $extension = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=127)
     */
    protected string $type = '';

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected int $fileSize = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", options={"default" : "LocalFile"})
     */
    protected string $storageType = 'LocalFile';

    /**
     * @var IFileMediaStorage|null
     */
    protected ?IFileMediaStorage $storage = null;

    public function __construct()
    {
        $this->name = GeneratorHelper::generateName();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @param bool $autoTrim
     * @return string
     */
    public function formatFileSize(int $decimals = 0, string $decPoint = '.', string $thousandsSep = ',', bool $autoTrim = true): string
    {
        $bytes = (string) $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        $formatted = number_format($bytes / (1024 ** $factor), $decimals, $decPoint, $thousandsSep);

        if ($autoTrim) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, ',');
        }

        return $formatted . ' ' . @ $units[$factor];
    }

    /**
     * @return IFileMediaStorage
     */
    public function getStorage(): IFileMediaStorage
    {
        if (! $this->storage) {
            $class = MediaStorageTypesProvider::resolveClassByType($this->storageType);
            $this->storage = new $class($this);
        }

        return $this->storage;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setStorageType(string $type): self
    {
        $this->storageType = $type;
        $this->storage = null;

        return $this;
    }

    /**
     * @return string
     */
    public function resolveTitle(): string
    {
        return $this->getTitle() ?: $this->getFilename();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        $title = $this->getTitle() ?: $this->getName();
        $parts = explode('-', Strings::webalize($title));

        if (end($parts) === $this->extension) {
            array_pop($parts);
        }

        return implode('-', $parts) . '.' . $this->extension;
    }

    /**
     * @param EntityManager $entityManager
     * @return $this
     */
    public function clone(EntityManager $entityManager): self
    {
        $clone = clone $this;
        $clone->name = GeneratorHelper::generateName();

        $clone->getStorage()->clone($this->getStorage());
        $entityManager->persist($clone);

        return $clone;
    }

    /**
     * @param DataMedium $media
     */
    public function preStore(DataMedium $media): void {}

    /**
     * @param DataMedium $media
     */
    public function postStore(DataMedium $media): void
    {
        $this->type = $media->getMimeType();
        $this->extension = $media->getExtension();
        $this->fileSize = $media->getSize();

        if ($basename = $media->getBasename()) {
            $this->title = Strings::truncate(pathinfo($basename, PATHINFO_FILENAME), 255);
        }
    }
}
