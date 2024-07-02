<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\SupplierOrderProduct;
use Doctrine\Common\Collections\Collection;

/**
 * @method SupplierOrderProduct|null find(int $id)
 * @method SupplierOrderProduct findOrException(int $id)
 * @method SupplierOrderProduct findOrNew(int $id)
 * @method SupplierOrderProduct findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|SupplierOrderProduct[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|SupplierOrderProduct[] findAll()
 */
class SupplierOrderProductRepository extends AbstractRepository
{
}
