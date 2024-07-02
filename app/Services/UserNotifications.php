<?php declare(strict_types=1);

    namespace App\Services;

    use App\Models\CashRegisterItem;
    use App\Models\Notification;
    use App\Models\User;
    use App\Services\Doctrine\EntityManager;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;
    use Nette\Application\LinkGenerator;
    use Nette\Application\UI\InvalidLinkException;

    class UserNotifications
    {
        /**
         * @param EntityManager $entityManager
         * @param LinkGenerator $linkGenerator
         */
        public function __construct(
            private readonly EntityManager $entityManager,
            private readonly LinkGenerator $linkGenerator,
        )
        {
        }

        /**
         * @param CashRegisterItem $cashRegisterItem
         * @return void
         * @throws InvalidLinkException
         */
        public function invokeAcceptRequestForEditCashRegisterItem(CashRegisterItem $cashRegisterItem): void
        {
            $user = $cashRegisterItem->getUser();
            $link = $this->linkGenerator->link('App:CashRegisterItem:edit', [
                'id' => $cashRegisterItem->getId()
            ]);

            $content = 'Admin ' . $cashRegisterItem->getManager()?->getFullName() . ' upravil žádost o editaci <a href="' . $link . '">tržby</a>, vytvořenou dne ' . $cashRegisterItem->getCreatedOn()->format('d.m.Y');

            $notification = new Notification($user, $content);
            $this->entityManager->persist($notification);

            $this->entityManager->flush();
        }

        /**
         * @return Collection
         */
        private function loadAdministrators(): Collection
        {
            return new ArrayCollection(
                $this->entityManager
                    ->createQueryBuilder()
                    ->select('user')
                    ->from(User::class, 'user')
                    ->where('user.role = :role')
                    ->setParameter('role', User::ROLE_ADMIN)
                    ->andWhere('user.blocked = false')
                    ->getQuery()
                    ->execute()
            );
        }
    }
