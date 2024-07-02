<?php declare(strict_types=1);

namespace App\Models\Interfaces;

use App\Services\DI;

interface IEntityOnDestroyCallback extends IEntity
{
    /**
     * @param DI $di
     * @return callable|null
     */
    public function onDestroy(DI $di): ?callable;
}
