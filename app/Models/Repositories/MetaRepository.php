<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Meta;
use Doctrine\Common\Collections\Collection;

/**
 * @method Meta|null find(int $id)
 * @method Meta findOrException(int $id)
 * @method Meta findOrNew(int $id)
 * @method Meta findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Meta[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Meta[] findAll()
 */
class MetaRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('m')->getQuery()->getResult();

        $list= [];
        /** @var Meta $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
