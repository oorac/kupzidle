<?php declare(strict_types=1);

namespace App\Media\Storages\Image;

use App\Services\Deadpool\Image as DeadpoolImage;
use App\Media\Storages\IMediaStorage;
use App\Media\DataMedium;

interface IImageMediaStorage extends IMediaStorage
{
    /**
     * @param string $name
     * @param string $extension
     */
    public static function forceDelete(string $name, string $extension): void;

    /**
     * @return DataMedium
     */
    public function getMedium(): DataMedium;

    /**
     * @param DataMedium $media
     * @return $this
     */
    public function store(DataMedium $media): self;

    /**
     * @param IImageMediaStorage $storage
     * @return $this
     */
    public function clone(IImageMediaStorage $storage): self;

    /**
     * @param string $class
     * @return void
     */
    public function convertTo(string $class): void;

    /**
     * @param array $arguments
     */
    public function resize(array $arguments): void;

    /**
     * @param DeadpoolImage $image
     * @return string
     */
    public function getResizeUrl(DeadpoolImage $image): string;

    /**
     * @return string
     */
    public function getDownloadUrl(): string;

    /**
     * @param callable|null $callback
     */
    public function download(?callable $callback = null): void;

    /**
     * @return void
     */
    public function delete(): void;
}
