<?php declare(strict_types=1);

    namespace App\Models\Repositories;

    use App\Models\Settings;
    use Doctrine\Common\Collections\Collection;

    /**
     * @method Settings|null find(int $id)
     * @method Settings findOrException(int $id)
     * @method Settings findOrNew(int $id)
     * @method Settings findOneBy(array $criteria, array $orderBy = null)
     * @method Collection|Settings[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     * @method Collection|Settings[] findAll()
     */
    class SettingsRepository extends AbstractRepository
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
