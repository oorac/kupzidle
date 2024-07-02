<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\SupplierOrder;
use Doctrine\Common\Collections\Collection;

/**
 * @method SupplierOrder|null find(int $id)
 * @method SupplierOrder findOrException(int $id)
 * @method SupplierOrder findOrNew(int $id)
 * @method SupplierOrder findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|SupplierOrder[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|SupplierOrder[] findAll()
 */
class SupplierOrderRepository extends AbstractRepository
{
}
