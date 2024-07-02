<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\ProductCategory;
use Doctrine\Common\Collections\Collection;

/**
 * @method ProductCategory|null find(int $id)
 * @method ProductCategory findOrException(int $id)
 * @method ProductCategory findOrNew(int $id)
 * @method ProductCategory findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|ProductCategory[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|ProductCategory[] findAll()
 */
class ProductCategoryRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('pc')->getQuery()->getResult();

        $list= [];
        /** @var ProductCategory $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
