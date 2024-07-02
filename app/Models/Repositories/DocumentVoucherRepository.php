<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\DocumentVoucher;
use Doctrine\Common\Collections\Collection;

/**
 * @method DocumentVoucher|null find(int $id)
 * @method DocumentVoucher findOrException(int $id)
 * @method DocumentVoucher findOrNew(int $id)
 * @method DocumentVoucher findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|DocumentVoucher[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|DocumentVoucher[] findAll()
 */
class DocumentVoucherRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('dv')->getQuery()->getResult();

        $list= [];
        /** @var DocumentVoucher $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
