<?php declare(strict_types=1);

    namespace App\Models\Repositories;

    use App\Exceptions\NotFoundException;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\ORM\EntityRepository;
    use Nette\Utils\Json;
    use Nette\Utils\JsonException;
    use Tracy\Debugger;

    abstract class AbstractRepository extends EntityRepository
    {
        /**
         * @param array $criteria
         * @param array|null $orderBy
         * @param null $limit
         * @param null $offset
         * @return Collection
         */
        public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): Collection
        {
            return new ArrayCollection(parent::findBy($criteria, $orderBy, $limit, $offset));
        }

        /**
         * @param int $id
         * @return object
         * @throws NotFoundException
         */
        public function findOrException(int $id): object
        {
            if (!$entity = $this->find($id)) {
                throw new NotFoundException(sprintf('Entity "%s::(%s)" cannot be found.', $this->getEntityName(), $id));
            }

            return $entity;
        }

        /**
         * @param int|null $id
         * @return object
         */
        public function findOrNew(?int $id = null): object
        {
            if (!$id || !$entity = $this->find($id)) {
                return $this->getNewEntity();
            }

            return $entity;
        }

        /**
         * @param mixed ...$args
         * @return object
         */
        public function getNewEntity(...$args): object
        {
            $class = $this->getEntityName();

            return new $class(...$args);
        }

        /**
         * @param array $criteria
         * @return object
         */
        public function findOneByOrNew(array $criteria): object
        {
            if (!empty($criteria) || !$entity = $this->findOneBy($criteria)) {
                return $this->getNewEntity();
            }

            return $entity;
        }

        /**
         * @param array $criteria
         * @param array $orderBy
         * @return object
         */
        public function findOneByOrException(array $criteria, array $orderBy = []): object
        {
            if (!$entity = $this->findOneBy($criteria, $orderBy)) {
                try {
                    $encoded = Json::encode($criteria);
                } catch (JsonException $exception) {
                    Debugger::log($exception);
                    $encoded = 'ENCODING ERROR';
                }

                throw new NotFoundException(sprintf('Entity "%s" cannot be found by given criteria: %s', $this->getEntityName(), $encoded));
            }

            return $entity;
        }
    }
