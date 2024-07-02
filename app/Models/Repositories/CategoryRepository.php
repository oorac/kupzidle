<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Category;
use Doctrine\Common\Collections\Collection;

/**
 * @method Category|null find(int $id)
 * @method Category findOrException(int $id)
 * @method Category findOrNew(int $id)
 * @method Category findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Category[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Category[] findAll()
 */
class CategoryRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('c')->getQuery()->getResult();

        $list= [];
        /** @var Category $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
