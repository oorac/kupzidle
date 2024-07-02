<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Transaction;
use Doctrine\Common\Collections\Collection;

/**
 * @method Transaction|null find(int $id)
 * @method Transaction findOrException(int $id)
 * @method Transaction findOrNew(int $id)
 * @method Transaction findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Transaction[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Transaction[] findAll()
 */
class TransactionRepository extends AbstractRepository
{
}
