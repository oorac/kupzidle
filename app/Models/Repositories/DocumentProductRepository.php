<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\DocumentProduct;
use Doctrine\Common\Collections\Collection;

/**
 * @method DocumentProduct|null find(int $id)
 * @method DocumentProduct findOrException(int $id)
 * @method DocumentProduct findOrNew(int $id)
 * @method DocumentProduct findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|DocumentProduct[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|DocumentProduct[] findAll()
 */
class DocumentProductRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('dp')->getQuery()->getResult();

        $list= [];
        /** @var DocumentProduct $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
