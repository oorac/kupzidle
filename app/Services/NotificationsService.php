<?php declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Models\Notification;
use App\Models\User;
use App\Security\SecurityUser;
use App\Services\Doctrine\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class NotificationsService
{
    public const DEFAULT_LIMIT = 20;

    /**
     * @param EntityManager $entityManager
     * @param SecurityUser $securityUser
     */
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly SecurityUser $securityUser,
    ) {}

    /**
     * @param int $limit
     * @param int|null $afterCursorID
     * @return array
     */
    public function fetch(int $limit = self::DEFAULT_LIMIT, ?int $afterCursorID = null): array
    {
        $data = [
            'limit' => $limit,
            'items' => [],
        ];

        $builder = $this->entityManager
            ->createQueryBuilder()
            ->select('notification')
            ->from(Notification::class, 'notification')
            ->andWhere('notification.user = :user')
            ->setParameter('user', $this->getUser())
            ->setMaxResults($limit)
            ->addOrderBy('notification.id', 'DESC');

        if ($afterCursorID) {
            $builder
                ->andWhere('notification.id < :afterCursorID')
                ->setParameter('afterCursorID', $afterCursorID);
        }

        $update = false;
        $lastCursorID = null;
        array_map(static function (Notification $notification) use (&$data, &$lastCursorID, &$update) {
            $data['items'][] = [
                'id' => $notification->getId(),
                'createdOn' => $notification->getCreatedOn()->format('j.n.Y, H:i:s'),
                'seenOn' => $notification->getSeenOn()?->format('j.n.Y, H:i:s'),
                'content' => $notification->getContent(),
            ];

            $lastCursorID = $notification->getId();

            if (! $notification->getSeenOn()) {
                $notification->setSeenOnNow();
                $update = true;
            }
        }, $builder->getQuery()->getResult());

        if ($update) {
            $this->entityManager->flush();
        }

        $count = count($data['items']);
        $data['count'] = $count;
        $data['lastCursorID'] = $lastCursorID;

        if ($count < $limit) {
            $data['hasNext'] = false;
        } else {
            try {
                $data['hasNext'] = (bool) $this->entityManager
                    ->createQueryBuilder()
                    ->select('COUNT(notification)')
                    ->from(Notification::class, 'notification')
                    ->andWhere('notification.user = :user')
                    ->setParameter('user', $this->getUser())
                    ->andWhere('notification.id < :lastCursorID')
                    ->setParameter('lastCursorID', $lastCursorID)
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleScalarResult();
            } catch (NoResultException|NonUniqueResultException) {
                $data['hasNext'] = false;
            }
        }

        return $data;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        try {
            return $this->entityManager
                ->createQueryBuilder()
                ->select('COUNT(notification.id)')
                ->from(Notification::class, 'notification')
                ->andWhere('notification.seenOn IS NULL')
                ->andWhere('notification.user = :user')
                ->setParameter('user', $this->getUser())
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return 0;
        }
    }

    /**
     * @return User
     */
    private function getUser(): User
    {
        if ($user = $this->securityUser->getIdentityUser()) {
            return $user;
        }

        throw new ValidationException('User is not logged!');
    }
}
