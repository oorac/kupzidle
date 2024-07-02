<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Customer;
use Doctrine\Common\Collections\Collection;

/**
 * @method Customer|null find(int $id)
 * @method Customer findOrException(int $id)
 * @method Customer findOrNew(int $id)
 * @method Customer findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Customer[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Customer[] findAll()
 */
class CustomerRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('c')->getQuery()->getResult();

        $list= [];
        /** @var Customer $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
