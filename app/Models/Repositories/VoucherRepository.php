<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Voucher;
use Doctrine\Common\Collections\Collection;

/**
 * @method Voucher|null find(int $id)
 * @method Voucher findOrException(int $id)
 * @method Voucher findOrNew(int $id)
 * @method Voucher findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Voucher[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Voucher[] findAll()
 */
class VoucherRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('v')->getQuery()->getResult();

        $list= [];
        /** @var Voucher $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

}
