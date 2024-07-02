<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Store;
use Doctrine\Common\Collections\Collection;

/**
 * @method Store|null find(int $id)
 * @method Store findOrException(int $id)
 * @method Store findOrNew(int $id)
 * @method Store findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Store[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Store[] findAll()
 */
class StoreRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('s')->getQuery()->getResult();

        $list= [];
        /** @var Store $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row->getTitle();
        }

        return $list;
    }
}
