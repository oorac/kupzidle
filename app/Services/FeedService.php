<?php declare(strict_types = 1);

namespace App\Services;

use App\Models\Label;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductParameter;
use App\Models\Repositories\ProductRepository;
use DOMDocument;
use DOMElement;

class FeedService
{
    private const XML_EXT = '.xml';

    private const EXPORT_FTP_ROOT = DIR_WWW . '/feed';

    /**
     * @var ProductRepository
     * @inject
     */
    public ProductRepository $productRepository;

    public function __construct(
        ProductRepository $productRepository
    )
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @return void
     */
    public function generate(): void
    {
        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;

        $root = $this->createAndAppendElement($dom, $dom,'items');

        /** @var Product $productEntity */
        foreach ($this->productRepository->findBy(['status' => Product::STATUS_SYNC]) as $productEntity) {
            if ($productEntity->getAvailabilityEshop() === null) {
                continue;
            }

            if ($productEntity->isArchive()) {
                continue;
            }

            if ($productEntity->getParent() !== null) {
                $categoryString = $this->getCategoryText($productEntity->getParent());
            } else {
                $categoryString = $this->getCategoryText($productEntity);
            }

            if (empty($categoryString)) {
                continue;
            }

            $item = $this->createAndAppendElement($dom, $root,'item');

            if ($productEntity->getParent() !== null) {
                $this->createAndAppendElement($dom, $item, 'item_group_id', $productEntity->getParent()->getProductId());
            }

            $this->createAndAppendElement($dom, $item, 'identity', $productEntity->getProductId());
            $this->createAndAppendElement($dom, $item, 'title', $productEntity->getTitle() ?? $productEntity?->getParent()?->getTitle());
            $this->createAndAppendElement($dom, $item, 'url', $productEntity->getUrl() ?? $productEntity?->getParent()?->getUrl());

            if ($productEntity->getStockEshop() > 0) {
                $this->createAndAppendElement($dom, $item, 'availability', 1);
            } else {
                $this->createAndAppendElement($dom, $item, 'availability', $productEntity->getStockEshop() ?? 0);
            }

            $availabilityRank = null;
            foreach (Product::AVAILABILITY_TEXT_ARRAY as $key => $availabilityEnum) {
                if ($productEntity->getAvailabilityEshop() === $key) {
                    $availabilityRank = Product::AVAILABILITY_TEXT_ARRAY[$key];

                    break;
                }

                if (str_contains($productEntity->getAvailabilityEshop(), $key)) {
                    $availabilityRank = Product::AVAILABILITY_TEXT_ARRAY[$key];
                    break;
                }
            }

            $this->createAndAppendElement($dom, $item, 'availability_rank', $availabilityRank ?? 15);
            $this->createAndAppendElement($dom, $item, 'availability_rank_text', $productEntity->getAvailabilityEshop());

            $this->createAndAppendElement($dom, $item, 'category', $categoryString);

            $this->createAndAppendElement($dom, $item, 'brand', $productEntity->getManufacturer() ?? $productEntity?->getParent()?->getManufacturer());
            $this->createAndAppendElement($dom, $item, 'price', $productEntity->getActualPrice());
            $this->createAndAppendElement($dom, $item, 'price_old', $productEntity->getStandardPrice());
            $this->createAndAppendElement($dom, $item, 'image_link_l', $productEntity->getImageLink());
            $this->createAndAppendElement($dom, $item, 'description', $productEntity->getDescription() ?? $productEntity?->getParent()?->getDescription());

            /** @var Label $labelEntity */
            foreach ($productEntity->getLabels() as $labelEntity) {
                $labels = $this->createAndAppendElement($dom, $item, 'labels');
                $label = $this->createAndAppendElement($dom, $labels, 'label');
                $this->createAndAppendElement($dom, $label, 'bg', $labelEntity->getBackground());
                $this->createAndAppendElement($dom, $label, 'fg', $labelEntity->getColor());
                $this->createAndAppendElement($dom, $label, 'text', $labelEntity->getName());
            }

            $this->createAndAppendElement($dom, $item, 'product_code', $productEntity->getProductCode());
            $this->createAndAppendElement($dom, $item, 'to_cart_id', $productEntity->getProductId());

            $parameters = $this->createAndAppendElement($dom, $item,'parameters');

            /** @var ProductParameter $productParameter */
            foreach ($productEntity->getProductParameters() as $productParameter) {
                $param = $this->createAndAppendElement($dom, $parameters, 'param');

                $this->createAndAppendElement($dom, $param, 'name', $productParameter->getName());
                $this->createAndAppendElement($dom, $param, 'value', $productParameter->getValue());
            }
        }

        $this->saveFile($dom, 'luigi');
    }

    /**
     * @param Product $productEntity
     * @return string
     */
    private function getCategoryText(Product $productEntity): string
    {
        $categoryString = "";
        $mainCategory = null;
        /** @var ProductCategory $productCategory */
        foreach ($productEntity->getProductCategories() as $productCategory) {
            if ($mainCategory === null && $productCategory->isMain()) {
                $mainCategory = $productCategory->getCategory()->getName();
            } elseif (empty($categoryString)) {
                $categoryString .= $productCategory->getCategory()->getName();
            } else {
                $categoryString .= ", " . $productCategory->getCategory()->getName();
            }
        }

        if (empty($categoryString)) {
            return $mainCategory ?? $categoryString;
        }

        if ($mainCategory !== null) {
            return $categoryString . ", " . $mainCategory;
        }

        return $categoryString;
    }

    /**
     * @param DOMDocument $xml
     * @param string $fileName
     * @return void
     */
    protected function saveFile(DOMDocument $xml, string $fileName): void
    {
        $file = $this->prepareExportFile($fileName);
        $xml->save($file);

        chmod($file, 0777);
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement|DOMDocument $rootElement
     * @param string $name
     * @param mixed|null $value
     * @return DOMElement
     */
    protected function createAndAppendElement(DOMDocument $dom, DOMElement|DOMDocument $rootElement, string $name, mixed $value = null)
    {
        if ($value === null) {
            $element = $dom->createElement($name);
        } else {
            $element = $dom->createElement($name, (string) $value);
        }
        $rootElement->appendChild($element);

        return $element;
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function prepareExportFile(string $fileName): string
    {
        return $this->getFilenamePath(self::EXPORT_FTP_ROOT, $fileName, self::XML_EXT);
    }

    /**
     * @param string $dir
     * @param string $filename
     * @param string $extension
     * @return string
     */
    private function getFilenamePath(string $dir, string $filename, string $extension): string
    {
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return $dir . '/' . $filename . $extension;
    }
}