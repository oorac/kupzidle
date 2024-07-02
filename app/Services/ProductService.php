<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Label;
use App\Models\Meta;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductMeta;
use App\Models\ProductParameter;
use App\Models\Repositories\CategoryRepository;
use App\Models\Repositories\LabelRepository;
use App\Models\Repositories\MetaRepository;
use App\Models\Repositories\ProductCategoryRepository;
use App\Models\Repositories\ProductMetaRepository;
use App\Models\Repositories\ProductParameterRepository;
use App\Models\Repositories\ProductRepository;
use App\Services\Doctrine\EntityManager;
use App\Services\UpGates\UpGatesService;
use Exception;
use RuntimeException;
use Tracy\ILogger;

class ProductService
{
    /**
     * @var ProductRepository
     * @inject
     */
    public ProductRepository $productRepository;

    /**
     * @var EntityManager
     * @inject
     */
    public EntityManager $entityManager;

    /**
     * @var ProductCategoryRepository
     * @inject
     */
    public ProductCategoryRepository $productCategoryRepository;

    /**
     * @var CategoryRepository
     * @inject
     */
    public CategoryRepository $categoryRepository;

    /**
     * @var ProductMetaRepository
     * @inject
     */
    public ProductMetaRepository $productMetaRepository;

    /**
     * @var MetaRepository
     * @inject
     */
    public MetaRepository $metaRepository;

    /**
     * @var ProductParameterRepository
     * @inject
     */
    public ProductParameterRepository $productParameterRepository;

    /**
     * @var LabelRepository
     * @inject
     */
    public LabelRepository $labelRepository;

    /**
     * @var ILogger
     * @inject
     */
    public ILogger $logger;

    /**
     * @param ProductRepository $productRepository
     * @param EntityManager $entityManager
     * @param ProductCategoryRepository $productCategoryRepository
     * @param CategoryRepository $categoryRepository
     * @param ProductMetaRepository $productMetaRepository
     * @param MetaRepository $metaRepository
     * @param ProductParameterRepository $productParameterRepository
     * @param LabelRepository $labelRepository
     * @param ILogger $logger
     */
    public function __construct(
        ProductRepository $productRepository,
        EntityManager $entityManager,
        ProductCategoryRepository $productCategoryRepository,
        CategoryRepository $categoryRepository,
        ProductMetaRepository $productMetaRepository,
        MetaRepository $metaRepository,
        ProductParameterRepository $productParameterRepository,
        LabelRepository $labelRepository,
        ILogger $logger
    )
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productMetaRepository = $productMetaRepository;
        $this->metaRepository = $metaRepository;
        $this->productParameterRepository = $productParameterRepository;
        $this->labelRepository = $labelRepository;
        $this->logger = $logger;
    }

    /**
     * @param array $category
     * @return Category|null
     */
    public function saveCategory(array $category): ?Category
    {
        $name = null;
        foreach ($category['descriptions'] as $description) {
            if ($description['language'] === 'cs') {
                $name = $description['name'];
            }
        }

        if ($name === null) {
            return null;
        }

        if (! $categoryEntity = $this->categoryRepository->findOneBy([
            'categoryId' => $category['category_id']
        ])) {
            $categoryEntity = (new Category())
                ->setCategoryId($category['category_id']);
            $this->entityManager->persist($categoryEntity);
        }

        $categoryEntity->setActive((bool) $category['active_yn'] && (bool) $category['show_in_menu_yn'])
            ->setName($name)
            ->setCode($category['code']);

        $this->entityManager->flush();

        return $categoryEntity;
    }

    /**
     * @param array $product
     * @return null|Product
     * @throws Exception
     */
    public function saveProduct(array $product): ?Product
    {
        try {
            $productEntity = $this->findProductEntity($product);

            if ($productEntity === null) {
                $productEntity = new Product();
                $this->entityManager->persist($productEntity);
            } else {
                $this->removeProductCategories($productEntity);
                $this->entityManager->flush();
            }

            $imageUrl = null;
            foreach ($product['images'] as $image) {
                if ((bool) $image['main_yn'] === true) {
                    $imageUrl = $image['url'];
                }
            }

            [$actualPrice, $purchasePrice, $standardPrice] = $this->savePrices($product);
            $this->saveProductCategory($productEntity, $product);
            $articleId = $this->saveProductMeta($productEntity, $product);

            $title = null;
            $productDescription = null;
            $productLongDescription = null;
            $url = null;

            foreach ($product['descriptions'] as $description) {
                if ($description['language'] === 'cs') {
                    $title = $description['title'];
                    $productDescription = $description['short_description'];
                    $productLongDescription = $description['long_description'];
                    $url = $description['url'];
                }
            }

            $productEntity->setProductId($product['product_id'])
                ->setProductCode($product['code'])
                ->setImageLink($imageUrl)
                ->setActualPrice($actualPrice ?? 0)
                ->setStandardPrice($standardPrice ?? 0)
                ->setPurchasePrice($purchasePrice ?? 0)
                ->setManufacturer($product['manufacturer'])
                ->setStockEshop($product['stock'])
                ->setAvailabilityEshop(((bool) $product['archived_yn']) === false ? Product::DEFAULT_AVAILABILITY_ESHOP : $product['availability'])
                ->setWeight($product['weight'] ?? 0)
                ->setTitle($title)
                ->setDescription($productDescription)
                ->setLongDescription($productLongDescription)
                ->setUrl($url)
                ->setEan($product['ean'])
                ->setStatus((bool) $product['active_yn'] === true ? Product::STATUS_SYNC : Product::STATUS_CREATED)
                ->setArticleId($articleId)
                ->setArchive((bool) $product['archived_yn']);

            $this->entityManager->flush();

            if (! empty($product['variants'])) {
                foreach ($product['variants'] as $variant) {
                    if (! $productVariant = $this->productRepository->findOneBy([
                        'productCode' => $variant['code']
                    ])) {
                        $productVariant = new Product();
                        $this->entityManager->persist($productVariant);
                    }

                    [$actualPriceVariant, $purchasePriceVariant, $standardPriceVariant] = $this->savePrices($product);
                    $articleIdVariant = $this->saveProductMeta($productEntity, $product);

                    $productVariant->setParent($productEntity)
                        ->setProductId($variant['variant_id'])
                        ->setProductCode($variant['code'])
                        ->setImageLink($variant['image'])
                        ->setActualPrice($actualPriceVariant ?? 0)
                        ->setStandardPrice($standardPriceVariant ?? 0)
                        ->setPurchasePrice($purchasePriceVariant ?? 0)
                        ->setStockEshop($variant['stock'])
                        ->setAvailabilityEshop($variant['availability'])
                        ->setUrl($url)
                        ->setTitle($title)
                        ->setDescription($productDescription)
                        ->setLongDescription($productLongDescription)
                        ->setManufacturer($product['manufacturer'])
                        ->setWeight($variant['weight'] ?? 0)
                        ->setEan($variant['ean'])
                        ->setStatus((bool) $variant['active_yn'] === true ? Product::STATUS_SYNC : Product::STATUS_CREATED)
                        ->setArticleId($articleIdVariant);
                }
                $this->entityManager->flush();
            }
        } catch (Exception $exception) {
            $this->logger->log(
                $exception->getMessage(),
                ILogger::WARNING
            );
            return null;
        }


        return $productEntity;
    }

    /**
     * Finds a product entity by product ID or code.
     *
     * @param array $product
     * @return Product|null
     */
    private function findProductEntity(array $product): ?Product
    {
        $productEntity = $this->productRepository->findOneBy(['productId' => $product['product_id']]);
        if ($productEntity === null) {
            $productEntity = $this->productRepository->findOneBy(['productCode' => $product['code']]);
        }
        return $productEntity;
    }

    /**
     * Removes all product categories associated with the given product entity.
     *
     * @param Product $productEntity
     * @return void
     */
    private function removeProductCategories(Product $productEntity): void
    {
        $productCategories = $this->productCategoryRepository->findBy(['product' => $productEntity]);

        if ($productCategories->isEmpty()) {
            return;
        }

        foreach ($productCategories as $productCategory) {
            $this->entityManager->remove($productCategory);
        }
    }

    /**
     * @param Product $productEntity
     * @param array $product
     * @return string|null
     */
    private function saveProductMeta(Product $productEntity, array $product): ?string
    {
        $listOldProductMeta = [];
        /** @var ProductMeta $productMeta */
        foreach ($this->productMetaRepository->findBy(['product' => $productEntity]) as $productMeta) {
            $listOldProductMeta[$productMeta->getMeta()->getCode()] = $productMeta;
        }

        $articleId = null;
        foreach ($product['metas'] as $meta) {
            if (! $metaEntity = $this->metaRepository->findOneBy([
                'code' => $meta['key']
            ])) {
                $metaEntity = (new Meta())
                    ->setCode($meta['key']);
                $this->entityManager->persist($metaEntity);
            }

            if ($meta['key'] === 'custom_key_1') {
                $articleId = (string) $meta['value'];
            }

            if (isset($listOldProductMeta[$meta['key']])) {
                /** @var ProductMeta $productMeta */
                $productMeta = $listOldProductMeta[$meta['key']];
                if (! in_array($meta['key'], [Meta::SK_SUPPLIER, Meta::SK_MOSS, Meta::SK_BRNO], true)) {
                    if (isset($meta['value'])) {
                        $productMeta->setValue((string) $meta['value']);
                    } else {
                        $productMeta->setValue((string) $meta['values']['cs']['value']);
                    }
                } elseif (! $productEntity->isMoneySync()) {
                    $productMeta->setValue((string) $meta['value']);
                }
                unset($listOldProductMeta[$meta['key']]);
            } else {
                $productMetaEntity = (new ProductMeta())
                    ->setProduct($productEntity)
                    ->setMeta($metaEntity);

                if (isset($meta['value'])) {
                    $productMetaEntity->setValue((string) $meta['value']);
                } else {
                    $productMetaEntity->setValue((string) $meta['values']['cs']['value']);
                }

                $this->entityManager->persist($productMetaEntity);
            }
        }

        /** @var ProductMeta $oldProductMeta */
        foreach ($listOldProductMeta as $oldProductMeta) {
            $this->entityManager->remove($oldProductMeta);
        }

        return $articleId;
    }

    /**
     * @param Product $productEntity
     * @param array $product
     * @return array
     */
    private function saveProductCategory(Product $productEntity, array $product): array
    {
        $listProductCategory = [];

        foreach ($product['categories'] as $category) {
            if (! $categoryEntity = $this->categoryRepository->findOneBy([
                'categoryId' => $category['category_id']
            ])) {
                $this->logger->log(sprintf('Neexistující kategorie pod id %s', $category['category_id']), ILogger::ERROR);
                continue;
            }

            $productCategoryEntity = (new ProductCategory())
                ->setProduct($productEntity)
                ->setCategory($categoryEntity)
                ->setPosition($category['position'])
                ->setMain((bool) $category['main_yn']);
            $this->entityManager->persist($productCategoryEntity);

            $listProductCategory[] = $productCategoryEntity;
        }

        return $listProductCategory;
    }

    /**
     * @param array $product
     * @return array
     */
    private function savePrices(array $product): array
    {
        $actualPrice = 0;
        $purchasePrice = 0;
        $standardPrice = 0;
        foreach ($product['prices'] as $price) {
            if ($price['language'] === 'cs') {
                $purchasePrice = $price['price_purchase'];
                $standardPrice = $price['price_common'];
                foreach ($price['pricelists'] as $pricelist) {
                    if ($pricelist['name'] === 'Výchozí') {
                        $actualPrice = $pricelist['price_with_vat'];
                        break;
                    }
                }
                break;
            }
        }

        return [$actualPrice, $purchasePrice, $standardPrice];
    }

    /**
     * @param array $product
     * @return void
     */
    public function saveParameter(array $product): void
    {
        if (! $productEntity = $this->productRepository->findOneBy([
            'productCode' => $product['code']
        ])) {
            return;
        }

        /** @var ProductParameter $productParameter */
        foreach ($this->productParameterRepository->findBy(['product' => $productEntity]) as $productParameter) {
            $productParameter->setSync(false);
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($productEntity);

        if (! isset($product['parameters'])) {
            return;
        }

        foreach ($product['parameters'] as $parameter) {
            foreach ($parameter['values'] as $value) {
                if (! $productParameter = $this->productParameterRepository->findOneBy([
                    'name' => $parameter['name']['cs'],
                    'product' => $productEntity,
                    'value' => $value['cs']
                ])) {
                    $productParameter = (new ProductParameter())
                        ->setProduct($productEntity)
                        ->setName($parameter['name']['cs'])
                        ->setValue($value['cs']);
                    $this->entityManager->persist($productParameter);
                }

                $productParameter->setSync(true);
            }
        }

        $this->entityManager->flush();

        foreach ($this->productParameterRepository->findBy(['product' => $productEntity, 'sync' => false]) as $productParameter) {
            $this->entityManager->remove($productParameter);
        }

        $this->entityManager->flush();
    }

    /**
     * @param array $product
     * @return void
     */
    public function saveLabel(array $product): void
    {
        if (! $productEntity = $this->productRepository->findOneBy([
            'productCode' => $product['code']
        ])) {
            return;
        }

        /** @var Label $label */
        foreach ($productEntity->getLabels() as $label) {
            $productEntity->removeLabel($label);
        }

        $this->entityManager->flush();

        if (! isset($product['labels'])) {
            return;
        }

        foreach ($product['labels'] as $label) {
            if ((bool) $label['active_yn'] === false) {
                continue;
            }

            if (isset($label['name']['cz'])) {
                $prefix = 'cz';
            } elseif (isset($label['name']['cs'])) {
                $prefix = 'cs';
            } else {
                continue;
            }

            if (! $labelEntity = $this->labelRepository->findOneBy([
                'name' => $label['name'][$prefix],
                'labelId' => $label['label_id']
            ])) {
                $labelEntity = (new Label())
                    ->setLabelId($label['label_id']);
                if ($label['name'][$prefix] !== null) {
                    $labelEntity->setName($label['name'][$prefix]);
                }

                $this->entityManager->persist($labelEntity);
            }

            if (str_contains(strtoupper($label['name'][$prefix]), 'DOPORUČUJEME')) {
                $labelEntity->setColor(Label::COLOR_RECOMMEND)
                    ->setBackground(Label::BACKGROUND_RECOMMEND);
            } elseif (str_contains(strtoupper($label['name'][$prefix]), 'DOPRAVA ZDARMA')) {
                $labelEntity->setColor(Label::COLOR_DELIVERY_FREE)
                    ->setBackground(Label::BACKGROUND_DELIVERY_FREE);
            } elseif (str_contains(strtoupper($label['name'][$prefix]), '%')) {
                $labelEntity->setColor(Label::COLOR_SALE)
                    ->setBackground(Label::BACKGROUND_SALE);
            }

            $productEntity->addLabel($labelEntity);
        }

        $this->entityManager->flush();
    }
}