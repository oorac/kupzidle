<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Package;
use Doctrine\Common\Collections\Collection;

/**
 * @method Package|null find(int $id)
 * @method Package findOrException(int $id)
 * @method Package findOrNew(int $id)
 * @method Package findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Package[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Package[] findAll()
 */
class PackageRepository extends AbstractRepository
{
}
