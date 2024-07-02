<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Predict;
use Doctrine\Common\Collections\Collection;

/**
 * @method Predict|null find(int $id)
 * @method Predict findOrException(int $id)
 * @method Predict findOrNew(int $id)
 * @method Predict findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Predict[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Predict[] findAll()
 */
class PredictRepository extends AbstractRepository
{
}
