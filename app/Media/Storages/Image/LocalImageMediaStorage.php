<?php declare(strict_types=1);

namespace App\Media\Storages\Image;

use App\Exceptions\ValidationException;
use App\Helpers\FileHelper;
use App\Helpers\ImageHelper;
use App\Helpers\UrlHelper;
use App\Media\DataMedium;
use App\Models\Image;
use App\Models\Interfaces\IEntityMedia;
use App\Providers\MediaStorageTypesProvider;
use App\Services\Deadpool\Image as DeadpoolImage;
use App\Services\DI;
use App\Services\Doctrine\EntityManager;
use App\Utils\Arrays;
use App\Utils\Errors;
use App\Utils\FileSystem;
use InvalidArgumentException;
use Nette\Utils\Image as NetteImage;
use Nette\Utils\ImageException;
use Nette\Utils\JsonException;
use Nette\Utils\UnknownImageFileException;

class LocalImageMediaStorage implements IImageMediaStorage
{
    public const DIR_IMAGES = DIR_WWW . DS . 'media' . DS . 'images';
    public const DIR_IMAGES_CACHE = DIR_WWW . DS . 'media' . DS . 'images-cache';

    /**
     * @var Image
     */
    private Image $entity;

    /**
     * @param IEntityMedia $entity
     */
    public function __construct(IEntityMedia $entity)
    {
        if (! $entity instanceof Image) {
            throw new ValidationException('Incorrect type');
        }

        $this->entity = $entity;
    }

    /**
     * @return array|null
     */
    public static function parseResizeUrlParameters(): ?array
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return null;
        }

        if (! preg_match('~/media/images-cache/(\w+)-(\w+)\.(\w+)~', $_SERVER['REQUEST_URI'], $parts)) {
            return null;
        }

        [, $name, $cipher, $extension] = $parts;

        $decoded = Errors::handle(static function () use ($cipher) {
            return pack('H*', $cipher);
        }, static function (int $number, string $string) {
            throw new InvalidArgumentException($string);
        });

        // backward compatibility – starting parameters with ID
        if (substr_count($decoded, '.') === 4) {
            [, $width, $height, $quality, $flags] = explode('.', $decoded);
        } else {
            [$width, $height, $quality, $flags] = explode('.', $decoded);
        }

        return [
            'name' => $name,
            'cipher' => $cipher,
            'width' => $width === '' ? null : (int) $width,
            'height' => $height === '' ? null : (int) $height,
            'quality' => (int) $quality,
            'flags' => (int) $flags,
            'extension' => $extension,
        ];
    }

    /**
     * @return DataMedium
     */
    public function getMedium(): DataMedium
    {
        return DataMedium::fromPath(self::DIR_IMAGES . DS . $this->entity->getName());
    }

    /**
     * @param DataMedium $media
     * @return $this
     * @throws ImageException
     */
    public function store(DataMedium $media): self
    {
        $this->entity->preStore($media);
        FileSystem::writeSafe(self::DIR_IMAGES . DS . $this->entity->getName(), $media->getData());
        $this->entity->postStore($media);

        return $this;
    }

    /**
     * @param IImageMediaStorage $storage
     * @return $this
     * @throws ImageException
     */
    public function clone(IImageMediaStorage $storage): self
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

        if (! is_subclass_of($class, IImageMediaStorage::class, true)) {
            throw new ValidationException(sprintf("Storage type must be subclass of `%s`, `%s` given.", IImageMediaStorage::class, $class));
        }

        /** @var IImageMediaStorage $new */
        $new = new $class($this->entity);
        $new->store($this->getMedium());

        $this->entity->setStorageType(MediaStorageTypesProvider::resolveTypeByClass($class));

//        EntityManager::getInstance()->pushToPostFlushStack(function () use ($class) {
//            DI::getInstance()
//                ->get(Bunny::class)
//                ->filterQueue(ImageStorageDeleteMediaTask::class, [
//                    $this->entity->getName(),
//                    $this->entity->getExtension(),
//                    MediaStorageTypesProvider::resolveTypeByClass($class)
//                ]);
//
//            $this->delete();
//        });
    }

    /**
     * @param array $arguments
     * @throws ImageException
     * @throws UnknownImageFileException
     */
    public function resize(array $arguments): void
    {
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24 * 365 * 10)));

        if (! file_exists(self::DIR_IMAGES_CACHE)) {
            FileSystem::createDir(self::DIR_IMAGES_CACHE);
            $this->buildCachingHtaccess();
        }

        $name = $arguments['name'];
        $cipher = $arguments['cipher'];
        $width = $arguments['width'];
        $height = $arguments['height'];
        $quality = $arguments['quality'];
        $extension = $arguments['extension'];
        $flags = $arguments['flags'];

        if (! $width && ! $height) {
            throw new ValidationException('Image height or width must be specified for resizing!');
        }

        $path = self::DIR_IMAGES_CACHE . DS . $name . '-' . $cipher . '.' . $extension;
        $type = ImageHelper::extensionToImageType($extension);
        $quality = ImageHelper::convertQuality($quality, $type);
        $instance = NetteImage::fromFile(self::DIR_IMAGES . DS . $this->entity->getName());

        // Palette image not supported by webp
        if ($type === NetteImage::WEBP) {
            $resource = $instance->getImageResource();
            imagepalettetotruecolor($resource);

            if (in_array($this->entity->getType(), ['image/png', 'image/webp'], true)) {
                imagealphablending($resource, true);
                imagesavealpha($resource, true);
            }

            $instance = new NetteImage($resource);
        }

        // PNG to other formats – set white background
        if (
            ! Arrays::in($type, [NetteImage::PNG, NetteImage::WEBP])
            && Arrays::in($this->entity->getExtension(), ['png', 'webp'])
        ) {
            $white = NetteImage::fromBlank($instance->getWidth(), $instance->getHeight(), NetteImage::rgb(255, 255, 255));
            $white->copy($instance, 0, 0, 0, 0, $instance->getWidth(), $instance->getHeight());
            $instance = $white;
        }

        // Black background PHP GD issue fix
        if ($flags & NetteImage::EXACT) {
            $instance->resize($width, $height, NetteImage::FILL);
            [$cut['x'], $cut['y'], $cut['width'], $cut['height']] = NetteImage::calculateCutout(
                $instance->getWidth(),
                $instance->getHeight(),
                '50%',
                '50%',
                $width ?: $instance->getWidth(),
                $height ?: $instance->getHeight()
            );

            $resource = NetteImage::fromBlank($cut['width'], $cut['height'], NetteImage::rgb(0, 0, 0, 127))->getImageResource();
            imagecopy($resource, $instance->getImageResource(), 0, 0, $cut['x'], $cut['y'], $cut['width'], $cut['height']);

            $newImage = new NetteImage($resource);
            $newImage->save($path, $quality, $type);
            $newImage->send($type, $quality);

            exit;
        }

        $instance->resize($width, $height, NetteImage::SHRINK_ONLY);
        $instance->save($path, $quality, $type);
        $instance->send($type, $quality);

        exit;
    }

    /**
     * @param DeadpoolImage $image
     * @return string
     */
    public function getResizeUrl(DeadpoolImage $image): string
    {
        $arguments = unpack('H*', implode('.', [
            $image->getWidth(),
            $image->getHeight(),
            $image->getQuality(),
            $image->getCompactedFlags(),
        ]))[1];

        if ($format = $image->getFormat()) {
            $extension = ImageHelper::imageTypeToExtension($format);
            $extension = $extension === 'webp' && ! ImageHelper::clientSupportsWebP() ? 'png' : $extension;
        } else {
            $extension = ImageHelper::clientSupportsWebP() ? 'webp' : $image->getSource()->getExtension();
        }

        return UrlHelper::getBaseUrl()
            . '/media/images-cache/'
            . $image->getSource()->getName()
            . '-'
            . $arguments
            . '.'
            . $extension;
    }

    /**
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return UrlHelper::getBaseUrl() . '/media/images/download/' . $this->entity->getName();
    }

    /**
     * @param callable|null $callback
     * @return never
     */
    public function download(?callable $callback = null): never
    {
        FileHelper::invokeOpen(self::DIR_IMAGES . DS . $this->entity->getName(), [
            'mime' => $this->entity->getType(),
            'filename' => $this->entity->getFilename(),
            'description' => $this->entity->getTitle(),
        ], $callback);
    }

    /**
     * @throws JsonException
     */
    public function delete(): void
    {
        if (! $class = MediaStorageTypesProvider::resolveClassByType( MediaStorageTypesProvider::resolveTypeByClass(self::class))) {
            return;
        }

        $class::forceDelete($this->entity->getName(),  $this->entity->getExtension());
    }

    /**
     * @param string $name
     * @param string $extension
     */
    public static function forceDelete(string $name, string $extension): void
    {
        if (FileSystem::exists(self::DIR_IMAGES . DS . $name)) {
            FileSystem::delete(self::DIR_IMAGES . DS . $name);
        }

        foreach (FileSystem::find(self::DIR_IMAGES_CACHE . DS . $name . '-*') as $file) {
            FileSystem::delete($file);
        }
    }

    /**
     * @return void
     */
    private function buildCachingHtaccess(): void
    {
        $path = self::DIR_IMAGES_CACHE . DS . '.htaccess';

        $content = '<IfModule mod_expires.c>' . PHP_EOL;
        $content .= TAB . 'ExpiresActive On' . PHP_EOL;
        $content .= TAB . 'ExpiresDefault "access plus 10 years"' . PHP_EOL;
        $content .= '</IfModule>' . PHP_EOL;

        $content .= '<IfModule mod_headers.c>' . PHP_EOL;
        $content .= TAB . 'Header set Cache-Control "max-age=31536050, public"' . PHP_EOL;
        $content .= '</IfModule>' . PHP_EOL;

        FileSystem::write($path, $content);
    }
}
