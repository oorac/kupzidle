<?php declare(strict_types=1);

namespace App\Media\Storages;

use App\Models\Interfaces\IEntityMedia;

interface IMediaStorage
{
    /**
     * @param IEntityMedia $entity
     */
    public function __construct(IEntityMedia $entity);
}
