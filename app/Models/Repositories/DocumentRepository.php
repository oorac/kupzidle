<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Document;
use Doctrine\Common\Collections\Collection;

/**
 * @method Document|null find(int $id)
 * @method Document findOrException(int $id)
 * @method Document findOrNew(int $id)
 * @method Document findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Document[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Document[] findAll()
 */
class DocumentRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('d')->getQuery()->getResult();

        $list= [];
        /** @var Document $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
