<?php declare(strict_types=1);

    namespace App\Models\Repositories;

    use App\Models\Image;
    use Doctrine\Common\Collections\Collection;

    /**
     * @method Image|null find(int $id)
     * @method Image findOrException(int $id)
     * @method Image findOrNew(int $id)
     * @method Image findOneBy(array $criteria, array $orderBy = null)
     * @method Collection|Image[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     * @method Collection|Image[] findAll()
     */
    class ImageRepository extends AbstractRepository
    {
    }
