<?php declare(strict_types=1);

    namespace App\Models\Repositories;

    use App\Models\Service;
    use Doctrine\Common\Collections\Collection;

    /**
     * @method Service|null find(int $id)
     * @method Service findOrException(int $id)
     * @method Service findOrNew(int $id)
     * @method Service findOneBy(array $criteria, array $orderBy = null)
     * @method Collection|Service[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     * @method Collection|Service[] findAll()
     */
    class ServiceRepository extends AbstractRepository
    {
        /**
         * @return array
         */
        public function getPairs(): array
        {
            $result = $this->createQueryBuilder('s')
                ->getQuery()->getResult();

            $addressIdNamePairs = [];
            foreach ($result as $row) {
                $addressIdNamePairs[$row->getTitle()] = $row->getValue();
            }

            return $addressIdNamePairs;
        }
    }
