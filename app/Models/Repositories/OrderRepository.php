<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Order;
use Doctrine\Common\Collections\Collection;

/**
 * @method Order|null find(int $id)
 * @method Order findOrException(int $id)
 * @method Order findOrNew(int $id)
 * @method Order findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Order[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Order[] findAll()
 */
class OrderRepository extends AbstractRepository
{
}
