<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\ProductMeta;
use Doctrine\Common\Collections\Collection;

/**
 * @method ProductMeta|null find(int $id)
 * @method ProductMeta findOrException(int $id)
 * @method ProductMeta findOrNew(int $id)
 * @method ProductMeta findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|ProductMeta[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|ProductMeta[] findAll()
 */
class ProductMetaRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('pm')->getQuery()->getResult();

        $list= [];
        /** @var ProductMeta $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
