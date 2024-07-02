<?php declare(strict_types=1);

namespace App\Models\Interfaces;

use App\Media\DataMedium;
use App\Media\Storages\IMediaStorage;
use App\Services\Doctrine\EntityManager;

interface IEntityMedia extends IEntity
{
    /**
     * @param EntityManager $entityManager
     * @return $this
     */
    public function clone(EntityManager $entityManager): self;

    /**
     * @return IMediaStorage
     */
    public function getStorage(): IMediaStorage;

    /**
     * @param string $type
     * @return $this
     */
    public function setStorageType(string $type): self;

    /**
     * @param DataMedium $media
     */
    public function preStore(DataMedium $media): void;

    /**
     * @param DataMedium $media
     */
    public function postStore(DataMedium $media): void;
}
