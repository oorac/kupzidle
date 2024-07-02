<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\OrderGroup;
use Doctrine\Common\Collections\Collection;

/**
 * @method OrderGroup|null find(int $id)
 * @method OrderGroup findOrException(int $id)
 * @method OrderGroup findOrNew(int $id)
 * @method OrderGroup findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|OrderGroup[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|OrderGroup[] findAll()
 */
class OrderGroupRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('og')->getQuery()->getResult();

        $list= [];
        /** @var OrderGroup $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
