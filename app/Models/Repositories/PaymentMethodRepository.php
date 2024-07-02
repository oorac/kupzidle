<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\PaymentMethod;
use Doctrine\Common\Collections\Collection;

/**
 * @method PaymentMethod|null find(int $id)
 * @method PaymentMethod findOrException(int $id)
 * @method PaymentMethod findOrNew(int $id)
 * @method PaymentMethod findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|PaymentMethod[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|PaymentMethod[] findAll()
 */
class PaymentMethodRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('pm')->getQuery()->getResult();

        $list= [];
        /** @var PaymentMethod $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
