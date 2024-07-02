<?php declare(strict_types=1);

    namespace App\Models;

    use App\Helpers\GeneratorHelper;
    use App\Helpers\ImageHelper;
    use App\Media\DataMedium;
    use App\Media\Storages\Image\IImageMediaStorage;
    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityConfiguration;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use App\Models\Interfaces\IEntityMedia;
    use App\Providers\MediaStorageTypesProvider;
    use App\Services\Doctrine\EntityManager;
    use App\Utils\Strings;
    use Doctrine\ORM\Mapping as ORM;
    use Nette\Utils\Image as NetteImage;
    use Nette\Utils\ImageException;
    use RuntimeException;
    use Throwable;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\ImageRepository")
     * @ORM\Table(name="image")
     * @ORM\HasLifecycleCallbacks
     */
    class Image implements IEntity, IEntityMedia
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;
        use EntityConfiguration;

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
         * @var integer
         *
         * @ORM\Column(type="integer")
         */
        protected int $width = 0;

        /**
         * @var integer
         *
         * @ORM\Column(type="integer")
         */
        protected int $height = 0;

        /**
         * @var string
         *
         * @ORM\Column(type="string", length=127)
         */
        protected string $type = '';

        /**
         * @var string
         *
         * @ORM\Column(type="string", length=10)
         */
        protected string $extension = '';

        /**
         * @var integer
         *
         * @ORM\Column(type="integer")
         */
        protected int $fileSize = 0;

        /**
         * @var string
         *
         * @ORM\Column(type="string", options={"default" : "LocalImage"})
         */
        protected string $storageType = 'LocalImage';

        /**
         * @var IImageMediaStorage|null
         */
        protected ?IImageMediaStorage $storage = null;

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
         * @param string $name
         * @return $this
         */
        public function setName(string $name): self
        {
            $this->name = $name;

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
         * @return int
         */
        public function getWidth(): int
        {
            return $this->width;
        }

        /**
         * @return int
         */
        public function getHeight(): int
        {
            return $this->height;
        }

        /**
         * @return string
         */
        public function getType(): string
        {
            return $this->type;
        }

        /**
         * @return string
         */
        public function getExtension(): string
        {
            return $this->extension;
        }

        /**
         * @return int
         */
        public function getFileSize(): int
        {
            return $this->fileSize;
        }

        /**
         * @return IImageMediaStorage
         */
        public function getStorage(): IImageMediaStorage
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
            return $this->title ?: $this->getFilename();
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
         * @return void
         * @throws ImageException
         */
        public function preStore(DataMedium $media): void
        {
            $media->update(function (DataMedium $media) {
                $data = $media->getData();

                try {
                    $handle = fopen('php://temp', 'rwb+');
                    fwrite($handle, $data);
                    rewind($handle);
                    $exif = @ exif_read_data($handle);
                    fclose($handle);
                } catch (Throwable) {
                    return $data;
                }

                if (empty($exif) || empty($exif['Orientation'])) {
                    return $data;
                }

                $image = NetteImage::fromString($data);

                // https://www.daveperrett.com/articles/2012/07/28/exif-orientation-handling-is-a-ghetto/#eh-exif-orientation
                switch ($exif['Orientation']) {
                    case 1:
                        break;

                    case 2:
                        $image->flip(IMG_FLIP_HORIZONTAL);
                        break;

                    case 3:
                        $image->rotate(180.0, 0);
                        break;

                    case 4:
                        $image->rotate(180.0, 0);
                        $image->flip(IMG_FLIP_HORIZONTAL);
                        break;

                    case 5:
                        $image->rotate(-90.0, 0);
                        $image->flip(IMG_FLIP_HORIZONTAL);
                        break;

                    case 6:
                        $image->rotate(-90.0, 0);
                        break;

                    case 7:
                        $image->rotate(90.0, 0);
                        $image->flip(IMG_FLIP_HORIZONTAL);
                        break;

                    case 8:
                        $image->rotate(90.0, 0);
                        break;
                }

                $type = ImageHelper::mimeTypeToImageType($media->getMimeType());

                return $image->toString($type, ImageHelper::convertQuality(100, $type));
            });
        }

        /**
         * @param DataMedium $media
         */
        public function postStore(DataMedium $media): void
        {
            $info = getimagesizefromstring($media->getData());
            if (! $info) {
                throw new RuntimeException('Broken image file!');
            }

            $this->width = $info[0];
            $this->height = $info[1];
            $this->type = $media->getMimeType();
            $this->extension = $media->getExtension();
            $this->fileSize = $media->getSize();

            if ($basename = $media->getBasename()) {
                $this->title = Strings::truncate(pathinfo($basename, PATHINFO_FILENAME), 255);
            }
        }
    }