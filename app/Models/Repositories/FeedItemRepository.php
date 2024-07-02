<?php declare(strict_types=1);

    namespace App\Models\Repositories;

    use App\Models\FeedItem;
    use Doctrine\Common\Collections\Collection;

    /**
     * @method FeedItem|null find(int $id)
     * @method FeedItem findOrException(int $id)
     * @method FeedItem findOrNew(int $id)
     * @method FeedItem findOneBy(array $criteria, array $orderBy = null)
     * @method Collection|FeedItem[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     * @method Collection|FeedItem[] findAll()
     */
    class FeedItemRepository extends AbstractRepository
    {
        /**
         * @return array
         */
        public function getPairs(): array
        {
            $result = $this->createQueryBuilder('fi')
                ->getQuery()->getResult();

            $list = [];
            foreach ($result as $row) {
                $list[$row->getId()] = $row;
            }

            return $list;
        }
    }
