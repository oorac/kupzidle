<?php declare(strict_types=1);

    namespace App\Models\Repositories;

    use App\Models\Feed;
    use Doctrine\Common\Collections\Collection;

    /**
     * @method Feed|null find(int $id)
     * @method Feed findOrException(int $id)
     * @method Feed findOrNew(int $id)
     * @method Feed findOneBy(array $criteria, array $orderBy = null)
     * @method Collection|Feed[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     * @method Collection|Feed[] findAll()
     */
    class FeedRepository extends AbstractRepository
    {
        /**
         * @return array
         */
        public function getPairs(): array
        {
            $result = $this->createQueryBuilder('f')
                ->getQuery()->getResult();

            $list = [];
            foreach ($result as $row) {
                $list[$row->getId()] = $row;
            }

            return $list;
        }
    }
