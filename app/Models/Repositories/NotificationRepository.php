<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Notification;
use App\Models\User;
use Doctrine\Common\Collections\Collection;

/**
 * @method Notification|null find(int $id)
 * @method Notification findOrException(int $id)
 * @method Notification findOrNew(int $id)
 * @method Notification findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Notification[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Notification[] findAll()
 */
class NotificationRepository extends AbstractRepository
{
    /**
     * @param User $user
     * @return int
     */
    public function findUnseenCount(User $user): int
    {
        $result = $this->_em
            ->createQueryBuilder()
            ->select('count(notification.id)')
            ->from(Notification::class,'notification')
            ->andWhere('notification.seenOn IS NULL')
            ->andWhere('notification.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return (int) $result[0][1];
    }

    /**
     * @param User $user
     * @param int $limit
     * @param int $offset
     * @return Collection
     */
    public function findByUser(User $user, int $limit, int $offset = 0): Collection
    {
        return $this->findBy([
            'user' => $user,
        ], [
            'seen' => 'ASC',
            'id' => 'DESC',
        ],
            $limit,
            $offset
        );
    }
}
