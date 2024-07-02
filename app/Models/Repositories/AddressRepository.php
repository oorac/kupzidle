<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Address;
use Doctrine\Common\Collections\Collection;

/**
 * @method Address|null find(int $id)
 * @method Address findOrException(int $id)
 * @method Address findOrNew(int $id)
 * @method Address findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Address[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Address[] findAll()
 */
class AddressRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPairs(): array
    {
        $result = $this->createQueryBuilder('a')->getQuery()->getResult();

        $list= [];
        /** @var Address $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row;
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getPairsSupplier(): array
    {
        $result = $this->createQueryBuilder('a')
            ->andWhere('a.type = :type')
            ->setParameter('type', Address::TYPE_ADDRESS_SUPPLIER)
            ->getQuery()->getResult();

        $list= [];
        /** @var Address $row */
        foreach ($result as $row) {
            $list[$row->getId()] = $row->getTitle();
        }

        return $list;
    }

    /**
     * @param string $countryCode
     * @param bool $depo
     * @return array
     */
    public function getByDepoAndCountryCode(string $countryCode = Address::COUNTRY_CODE_CZ, bool $depo = true): array
    {
        $result = $this->createQueryBuilder('a')
            ->andWhere('a.countryCode = :countryCode')
            ->andWhere('a.type = :type')
            ->setParameter('countryCode', $countryCode)
            ->setParameter('type', Address::TYPE_ADDRESS_DEPO)
            ->getQuery()->getResult();

        $pairs= [];
        /** @var Address $row */
        foreach ($result as $row) {
            $pairs[$row->getId()] = $row->getTitle();
        }

        return $pairs;
    }

}
