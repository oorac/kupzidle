<?php declare(strict_types=1);

    namespace App\Models\Repositories;

    use App\Models\Message;
    use Doctrine\Common\Collections\Collection;

    /**
     * @method Message|null find(int $id)
     * @method Message findOrException(int $id)
     * @method Message findOrNew(int $id)
     * @method Message findOneBy(array $criteria, array $orderBy = null)
     * @method Collection|Message[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     * @method Collection|Message[] findAll()
     */
    class MessageRepository extends AbstractRepository
    {
    }
