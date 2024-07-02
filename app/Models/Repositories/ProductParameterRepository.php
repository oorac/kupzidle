<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\ProductParameter;
use Doctrine\Common\Collections\Collection;

/**
 * @method ProductParameter|null find(int $id)
 * @method ProductParameter findOrException(int $id)
 * @method ProductParameter findOrNew(int $id)
 * @method ProductParameter findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|ProductParameter[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|ProductParameter[] findAll()
 */
class ProductParameterRepository extends AbstractRepository
{
}
