<?php declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Meta;
use App\Models\Product;
use App\Models\ProductMeta;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method Product|null find(int $id)
 * @method Product findOrException(int $id)
 * @method Product findOrNew(int $id)
 * @method Product findOneBy(array $criteria, array $orderBy = null)
 * @method Collection|Product[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Collection|Product[] findAll()
 */
class ProductRepository extends AbstractRepository
{
    /**
     * @param string $productCode
     * @return Product|null
     * @throws NonUniqueResultException
     */
    public function findOneByProductCode(string $productCode): ?Product
    {
        return $this->createQueryBuilder('p')
            ->where('p.productCode = :productCode')
            ->setParameter('productCode', $productCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $articleId
     * @return Product|null
     * @throws NonUniqueResultException
     */
    public function findOneByArticleId(string $articleId): ?Product
    {
        return $this->createQueryBuilder('p')
            ->where('p.articleId = :articleId')
            ->setParameter('articleId', $articleId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Product $product
     * @return array
     */
    public function findProductMetasWithSpecificCodes(Product $product): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('pm')
            ->from(ProductMeta::class, 'pm')
            ->innerJoin('pm.meta', 'm')
            ->where('pm.product = :productId')
            ->andWhere($qb->expr()->in('m.code', [
                Meta::SK_MOSS,
                Meta::SK_BRNO,
                Meta::SK_SUPPLIER,
            ]))
            ->setParameter('productId', $product);

        return $qb->getQuery()->getResult();
    }
}
