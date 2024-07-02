<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\OrderProduct;
use Doctrine\Common\Collections\Collection;

/**
 * @method OrderProduct|null find(int $id)
 * @method OrderProduct findOrException(int $id)
 * @method OrderProduct findOrNew(int $id)
 * @method OrderProduct findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|OrderProduct[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|OrderProduct[] findAll()
 */
class OrderProductRepository extends AbstractRepository
{
}
