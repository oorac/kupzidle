<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Label;
use Doctrine\Common\Collections\Collection;

/**
 * @method Label|null find(int $id)
 * @method Label findOrException(int $id)
 * @method Label findOrNew(int $id)
 * @method Label findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Label[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Label[] findAll()
 */
class LabelRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('l')->getQuery()->getResult();

        $list= [];
        /** @var Label $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
