<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Currency;
use Doctrine\Common\Collections\Collection;

/**
 * @method Currency|null find(int $id)
 * @method Currency findOrException(int $id)
 * @method Currency findOrNew(int $id)
 * @method Currency findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Currency[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Currency[] findAll()
 */
class CurrencyRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('c')->getQuery()->getResult();

        $list= [];
        /** @var Currency $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
