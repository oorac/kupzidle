<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\File;
use Doctrine\Common\Collections\Collection;

/**
 * @method File|null find(int $id)
 * @method File findOrException(int $id)
 * @method File findOrNew(int $id)
 * @method File findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|File[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|File[] findAll()
 */
class FileRepository extends AbstractRepository
{
}
