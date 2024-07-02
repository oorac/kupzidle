<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\ProductStore;
use Doctrine\Common\Collections\Collection;

/**
 * @method ProductStore|null find(int $id)
 * @method ProductStore findOrException(int $id)
 * @method ProductStore findOrNew(int $id)
 * @method ProductStore findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|ProductStore[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|ProductStore[] findAll()
 */
class ProductStoreRepository extends AbstractRepository
{
}
