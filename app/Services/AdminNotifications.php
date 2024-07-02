<?php declare(strict_types=1);

    namespace App\Services;

    use App\Models\CashRegister;
    use App\Models\CashRegisterItem;
    use App\Models\Currency;
    use App\Models\Notification;
    use App\Models\User;
    use App\Services\Doctrine\EntityManager;
    use Nette\Application\LinkGenerator;
    use Nette\Application\UI\InvalidLinkException;

    class AdminNotifications
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
        public function invokeRequestForEditCashRegisterItem(CashRegisterItem $cashRegisterItem): void
        {
            $link = $this->linkGenerator->link('Admin:CashRegisterItem:edit', [
                'id' => $cashRegisterItem->getId()
            ]);

            foreach ($this->loadManagers($cashRegisterItem->getCashRegister()) as $user) {
                $content = 'Uživatel ' . $cashRegisterItem->getUser()->getFullName() . ' žádá o povolení opravy <a href="' . $link . '">tržby</a>, vytvořenou dne ' . $cashRegisterItem->getCreatedOn()->format('d.m.Y');

                $notification = new Notification($user, $content);
                $this->entityManager->persist($notification);
            }

            $this->entityManager->flush();
        }

        /**
         * @param CashRegisterItem $cashRegisterItem
         * @return void
         * @throws InvalidLinkException
         */
        public function invokeApprovedCashRegisterItem(CashRegisterItem $cashRegisterItem): void
        {
            $link = $this->linkGenerator->link('Admin:CashRegisterItem:edit', [
                'id' => $cashRegisterItem->getId()
            ]);

            foreach ($this->loadManagers($cashRegisterItem->getCashRegister()) as $user) {
                $content = 'V ' . $cashRegisterItem->getCreatedOn()->format('d.m.Y H:i:s') . ' uživatel ' . $cashRegisterItem->getUser()->getFullName() . ' vytvořil novou <a href="' . $link . '">tržbu</a>, kterou je potřeba schválit.' ;

                $notification = new Notification($user, $content);
                $this->entityManager->persist($notification);
            }

            $this->entityManager->flush();
        }

        /**
         * @param User $userEntity
         * @return void
         * @throws InvalidLinkException
         */
        public function invokeRegisteredNewUser(User $userEntity): void
        {
            $link = $this->linkGenerator->link('Admin:List:User:edit', [
                'id' => $userEntity->getId()
            ]);

            foreach ($this->loadAdministrators() as $user) {
                $content = 'Uživatel ' . $userEntity->getFullName() . ' <a href="' . $link . '">žádá o povolení do systému</a>, dne ' . $userEntity->getCreatedOn()->format('d.m.Y');

                $notification = new Notification($user, $content);
                $this->entityManager->persist($notification);
            }

            $this->entityManager->flush();
        }

        /**
         * @param Currency $currency
         * @param User $userEntity
         * @return void
         */
        public function invokeRequestCreateCoins(Currency $currency, User $userEntity): void
        {
            foreach ($this->loadAdministrators() as $user) {
                $content = 'Uživatel ' . $userEntity->getFullName() . ' se pokusil přidat pokladnu pro měnu ' . $currency->getTitle() . ', která nemá založené žádné mince';

                $notification = new Notification($user, $content);
                $this->entityManager->persist($notification);
            }

            $this->entityManager->flush();
        }

        /**
         * @return array
         */
        private function loadAdministrators(): array
        {
            return $this->entityManager
                ->createQueryBuilder()
                ->select('user')
                ->from(User::class, 'user')
                ->where('user.role = :role')
                ->setParameter('role', User::ROLE_ADMIN)
                ->getQuery()
                ->getResult();
        }

        /**
         * @param CashRegister $cashRegister
         * @return array
         */
        private function loadManagers(CashRegister $cashRegister): array
        {
            return $this->entityManager
                ->createQueryBuilder()
                ->select('user')
                ->from(User::class, 'user')
                ->innerJoin('user.cashRegisters', 'cashRegister')
                ->where('user.role = :role')
                ->andWhere('cashRegister = :cashRegister')
                ->setParameter('role', User::ROLE_MANAGER)
                ->setParameter('cashRegister', $cashRegister)
                ->getQuery()
                ->getResult();
        }
    }
