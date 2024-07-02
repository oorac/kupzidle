<?php declare(strict_types=1);

    namespace App\Models\Repositories;

    use App\Models\User;
    use Doctrine\Common\Collections\Collection;

    /**
     * @method User|null find(int $id)
     * @method User findOrException(int $id)
     * @method User findOrNew(int $id)
     * @method User findOneBy(array $criteria, array $orderBy = null)
     * @method Collection|User[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     * @method Collection|User[] findAll()
     */
    class UserRepository extends AbstractRepository
    {
        /**
         * @return array
         */
        public function getPairs(): array
        {
            $result = $this->createQueryBuilder('u')
                ->andWhere('u.blocked IS NULL')
                ->andWhere('u.activateOn IS NOT NULL')
                ->getQuery()->getResult();

            $addressIdNamePairs = [];
            foreach ($result as $row) {
                $addressIdNamePairs[$row->getId()] = $row->getFullName();
            }

            return $addressIdNamePairs;
        }
    }
