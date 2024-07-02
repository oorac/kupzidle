<?php declare(strict_types=1);

namespace App\Providers;

use App\Media\Storages\File\LocalFileMediaStorage;
use App\Media\Storages\Image\LocalImageMediaStorage;
use App\Media\Storages\Image\TemporaryExternalImageMediaStorage;

class MediaStorageTypesProvider
{
    private const TYPES = [
        'LocalImage' => LocalImageMediaStorage::class,
        'LocalFile' => LocalFileMediaStorage::class,
        'TemporaryExternalImage' => TemporaryExternalImageMediaStorage::class,
    ];

    /**
     * @param string $type
     * @return string|null
     */
    public static function resolveClassByType(string $type): ?string
    {
        return self::TYPES[$type] ?? null;
    }

    /**
     * @param string $class
     * @return string|null
     */
    public static function resolveTypeByClass(string $class): ?string
    {
        return array_search($class, self::TYPES, true) ?: null;
    }
}
