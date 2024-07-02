<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\DeliveryMethod;
use Doctrine\Common\Collections\Collection;

/**
 * @method DeliveryMethod|null find(int $id)
 * @method DeliveryMethod findOrException(int $id)
 * @method DeliveryMethod findOrNew(int $id)
 * @method DeliveryMethod findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|DeliveryMethod[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|DeliveryMethod[] findAll()
 */
class DeliveryMethodRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('dm')->getQuery()->getResult();

        $list= [];
        /** @var DeliveryMethod $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
