<?php declare(strict_types = 1);

namespace App\Services;

use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\Product;
use App\Models\ProductParameter;
use App\Models\ProductStore;
use App\Models\Repositories\ProductParameterRepository;
use App\Models\Repositories\ProductRepository;
use App\Models\Repositories\ProductStoreRepository;
use App\Models\Repositories\StoreRepository;
use App\Models\Store;
use App\Services\Doctrine\EntityManager;
use App\Utils\FileSystem;
use DateTime;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class XmlParser
{
    /**
     * @var EntityManager
     * @inject
     */
    public EntityManager $entityManager;

    /**
     * @var ProductRepository
     * @inject
     */
    public ProductRepository $productRepository;

    /**
     * @var ProductStoreRepository
     * @inject
     */
    public ProductStoreRepository $productStoreRepository;

    /**
     * @var StoreRepository
     * @inject
     */
    public StoreRepository $storeRepository;

    /**
     * @var ProductParameterRepository
     * @inject
     */
    public ProductParameterRepository $productParameterRepository;

    /**
     * @param EntityManager $entityManager
     * @param ProductRepository $productRepository
     * @param ProductStoreRepository $productStoreRepository
     * @param StoreRepository $storeRepository
     * @param ProductParameterRepository $productParameterRepository
     */
    public function __construct(
        EntityManager $entityManager,
        ProductRepository $productRepository,
        ProductStoreRepository $productStoreRepository,
        StoreRepository $storeRepository,
        ProductParameterRepository $productParameterRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->productStoreRepository = $productStoreRepository;
        $this->storeRepository = $storeRepository;
        $this->productParameterRepository = $productParameterRepository;
    }

    /**
     * @param Feed $feed
     * @return bool
     * @throws GuzzleException
     */
    public function processDownload(Feed $feed): bool
    {
        $client = new Client();

        if ($feed->getUsername() !== null || $feed->getPassword() !== null) {
            $response = $client->request('GET', $feed->getUrl(), [
                'auth' => [$feed->getUsername(), $feed->getPassword()]
            ]);
        } else {
            $response = $client->request('GET', $feed->getUrl());
        }

        if ($response->getStatusCode() === 200) {
            $xmlContent = (string) $response->getBody();
            $name = $feed->getSupplier()?->getId() ? $feed->getSupplier()?->getId() . '.xml' : $feed->getOutputName();
            $xmlFile = Feed::DIR_FEED_XML . $name;
            if (! FileSystem::isFile($xmlFile)) {
                FileSystem::touch($xmlFile, 0777);
            }

            FileSystem::write($xmlFile, $xmlContent);
            $feedItem = (new FeedItem())
                ->setUrl($feed->getUrl())
                ->setFeed($feed)
                ->setDate(new DateTime());
            if ($feed->getSubType() === Feed::SUBTYPE_PRODUCT) {
                if ($this->parserFeed($xmlFile, Feed::DIR_FEED_XSLT . $feed->getXslFileName(), $feed) && FileSystem::isFile(Feed::DIR_FEED_OUTPUT . $feed->getOutputName())) {
                    $this->saveProduct(Feed::DIR_FEED_OUTPUT . $feed->getOutputName(), $feed);
                    $feedItem->setDownload(true);
                } else {
                    $feedItem->setDownload(false);
                }
            } else {
                $this->saveOrder(Feed::DIR_FEED_OUTPUT . $feed->getOutputName(), $feed);
            }

            $this->entityManager->persist($feedItem);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param string $filePath
     * @param Feed $feed
     * @return void
     */
    private function saveEshopProduct(string $filePath, Feed $feed): void
    {
        $xml = new DOMDocument();
        $xml->loadXML(FileSystem::read($filePath));

        $xpath = new DOMXPath($xml);
        $products = $xpath->query('//PRODUCTS/PRODUCT');

        foreach ($products as $product) {
            $productCode = $xpath->query('CODE', $product)->item(0)?->nodeValue;
            $productId = $xpath->query('PRODUCT_ID', $product)->item(0)?->nodeValue;
            $activeOnEshop = (bool) $xpath->query('ACTIVE_YN', $product)->item(0)?->nodeValue;
            $labels = $xpath->query('LABELS/LABEL', $product);
            $parameters = $xpath->query('PARAMETERS/PARAMETER', $product);
            $title = $xpath->query('DESCRIPTIONS/DESCRIPTION[@language="cz"]/TITLE', $product)->item(0)?->nodeValue;
            $manufacturer = $xpath->query('MANUFACTURER', $product)->item(0)?->nodeValue;
            $availabilityEshop = $xpath->query('AVAILABILITY', $product)->item(0)?->nodeValue;
            $stockEshop = $xpath->query('STOCK', $product)->item(0)?->nodeValue;
            $purchasePrice = (float) $xpath->query('PRICES/PRICE/PRICE_PURCHASE', $product)->item(0)?->nodeValue;
            $standardPrice = (float) $xpath->query('PRICES/PRICE/PRICE_COMMON', $product)->item(0)?->nodeValue;
            $actualPrice = (float) $xpath->query('PRICES/PRICE/PRICELIST/PRICE_WITH_VAT', $product)->item(0)?->nodeValue;
            $mainImageUrl = $xpath->query('IMAGES/IMAGE[MAIN_YN="1"]/URL', $product)->item(0)?->nodeValue;

            if (! $productEntity = $this->productRepository->findOneBy([
                'productCode' => $productCode,
                'supplier' => $feed->getSupplier()
            ])) {
                $productEntity = new Product();
                $this->entityManager->persist($productEntity);
            } else {
                /** @var ProductParameter $productParameter */
                foreach ($this->productParameterRepository->findBy(['product' => $productEntity]) as $productParameter) {
                    $this->entityManager->remove($productParameter);
                }
            }

            $labelString = '';
            foreach ($labels as $label) {
                if ((bool) $xpath->query('ACTIVE_YN', $label)->item(0)?->nodeValue) {
                    if (empty($labelString)) {
                        $labelString .= $xpath->query('NAME', $label)->item(0)?->nodeValue;
                    } else {
                        $labelString .= ', ' . $xpath->query('NAME', $label)->item(0)?->nodeValue;
                    }
                }
            }

            foreach ($parameters as $parameter) {
                $name = $xpath->query('NAME', $parameter)->item(0)?->nodeValue;
                $value = $xpath->query('VALUE', $parameter)->item(0)?->nodeValue;

                if (! $this->productParameterRepository->findOneBy([
                    'product' => $productEntity,
                    'name' => $name,
                    'value' => $value
                ])) {
                    $productParameter = (new ProductParameter())
                        ->setProduct($productEntity)
                        ->setName($name)
                        ->setValue($value);
                    $this->entityManager->persist($productParameter);
                }
            }

            $productEntity
                ->setProductCode($productCode)
                ->setSupplier($feed->getSupplier())
                ->setProductId($productId)
                ->setStatus($activeOnEshop === true ? Product::STATUS_SYNC : Product::STATUS_CREATED)
                ->setLabels($labelString)
                ->setTitle($title)
                ->setManufacturer($manufacturer)
                ->setAvailabilityEshop($availabilityEshop)
                ->setStockEshop($stockEshop)
                ->setPurchasePrice($purchasePrice)
                ->setStandardPrice($standardPrice)
                ->setActualPrice($actualPrice)
                ->setImageLink($mainImageUrl);
        }

        $this->entityManager->flush();
    }

    /**
     * @param string $filePath
     * @param Feed $feed
     * @return void
     */
    private function saveProduct(string $filePath, Feed $feed): void
    {
        $xml = new DOMDocument();
        $xml->loadXML(FileSystem::read($filePath));

        $xpath = new DOMXPath($xml);
        $products = $xpath->query('//ArtiklList/Artikl');

        if (! $store = $this->storeRepository->findOneBy([
            'code' => Store::DEFAULT_SUPPLIER_STORE_CODE
        ])) {
           return;
        }

        foreach ($products as $product) {
            $productCode = $xpath->query('Kod', $product)->item(0)?->nodeValue;
            $title = $xpath->query('Nazev', $product)->item(0)?->nodeValue;
            $supplierCode = $xpath->query('Katalog', $product)->item(0)?->nodeValue;
            $plu = $xpath->query('PLU', $product)->item(0)?->nodeValue;
            $note = $xpath->query('Poznamka', $product)->item(0)?->nodeValue;
            $description = $xpath->query('Popis', $product)->item(0)?->nodeValue;
            $package = (int) $xpath->query('Baleni', $product)->item(0)?->nodeValue;
            $stockSupplier = (float) $xpath->query('SklademIDodavatele_UserData', $product)->item(0)?->nodeValue;
            $deliveryCount = (int) $xpath->query('DodaciLhuta/Doba', $product)->item(0)?->nodeValue;

            if (! $productEntity = $this->productRepository->findOneBy([
                'productCode' => $productCode,
                'supplier' => $feed->getSupplier()
            ])) {
                $productEntity = new Product();
                $this->entityManager->persist($productEntity);
            }

            $productEntity->setTitle($title)
                ->setSupplier($feed->getSupplier())
                ->setPackage($package)
                ->setPlu($plu)
                ->setNote($note)
                ->setDescription($description)
                ->setDeliveryCount($deliveryCount)
                ->setSupplierCode($supplierCode)
                ->setProductCode($productCode);

            if (! $productStore = $this->productStoreRepository->findOneBy([
                'store' => $store,
                'product' => $productEntity
            ])) {
                $productStore = (new ProductStore())
                    ->setProduct($productEntity)
                    ->setStore($store);
                $this->entityManager->persist($productStore);
            }

            $productStore->setQuantity($stockSupplier);
        }

        $this->entityManager->flush();
    }

    /**
     * @param string $filePath
     * @param Feed $feed
     * @return void
     */
    private function saveOrder(string $filePath, Feed $feed): void
    {
        $xml = new DOMDocument();
        $xml->loadXML(FileSystem::read($filePath));

        $xpath = new DOMXPath($xml);
        $orders = $xpath->query('//ORDERS/ORDER');

        foreach ($orders as $order) {
            $orderNumber = $xpath->query('HEADER/CODE', $order)->item(0)?->nodeValue;
            $status = $xpath->query('HEADER/STATUS', $order)->item(0)?->nodeValue;
            $paid = (bool) $xpath->query('HEADER/PAID/PAID_YN', $order)->item(0)?->nodeValue;
            $date = new DateTime($xpath->query('HEADER/CREATED_AT', $order)->item(0)?->nodeValue);
            $currency = $xpath->query('HEADER/CURRENCY', $order)->item(0)?->nodeValue;
            $totalPriceWithVat = $xpath->query('HEADER/TOTAL_PRICE_WITH_VAT', $order)->item(0)?->nodeValue;
            $customerId = $xpath->query('CUSTOMER/CUSTOMER_ID', $order)->item(0)?->nodeValue;
            $customerCode = $xpath->query('CUSTOMER/CUSTOMER_CODE', $order)->item(0)?->nodeValue;
            $firstName = $xpath->query('CUSTOMER/FIRSTNAME', $order)->item(0)?->nodeValue;
            $lastName = $xpath->query('CUSTOMER/SURNAME', $order)->item(0)?->nodeValue;
            $email = $xpath->query('CUSTOMER/COMMUNICATION/EMAIL', $order)->item(0)?->nodeValue;
            $phone = $xpath->query('CUSTOMER/COMMUNICATION/PHONE', $order)->item(0)?->nodeValue;
            $isCompany = (bool) $xpath->query('CUSTOMER/COMPANY_YN', $order)->item(0)?->nodeValue;
            $companyName = $xpath->query('CUSTOMER/COMPANY/NAME', $order)->item(0)?->nodeValue;
            $crnId = $xpath->query('CUSTOMER/COMPANY/COMPANY_NUMBER', $order)->item(0)?->nodeValue;
            $vatId = $xpath->query('CUSTOMER/COMPANY/VAT_NUMBER', $order)->item(0)?->nodeValue;
            $billingAddressStreet = $xpath->query('CUSTOMER/ADDRESSES/BILLING/STREET', $order)->item(0)?->nodeValue;
            $billingAddressCity = $xpath->query('CUSTOMER/ADDRESSES/BILLING/CITY', $order)->item(0)?->nodeValue;
            $billingAddressState = $xpath->query('CUSTOMER/ADDRESSES/BILLING/STATE', $order)->item(0)?->nodeValue;
            $billingAddressZipCode = $xpath->query('CUSTOMER/ADDRESSES/BILLING/ZIP_CODE', $order)->item(0)?->nodeValue;
            $billingAddressCountry = $xpath->query('CUSTOMER/ADDRESSES/BILLING/COUNTRY_ID', $order)->item(0)?->nodeValue;

            $transportCode = $xpath->query('SHIPMENT/CODE', $order)->item(0)?->nodeValue;
            $transportName = $xpath->query('SHIPMENT/NAME', $order)->item(0)?->nodeValue;
            $transportPriceWithVat = $xpath->query('SHIPMENT/PRICE_WITH_VAT', $order)->item(0)?->nodeValue;
            $transportVAT = $xpath->query('SHIPMENT/VAT', $order)->item(0)?->nodeValue;

            $paymentCode = $xpath->query('PAYMENT/CODE', $order)->item(0)?->nodeValue;
            $paymentName = $xpath->query('PAYMENT/NAME', $order)->item(0)?->nodeValue;
            $paymentPriceWithVat = $xpath->query('PAYMENT/PRICE_WITH_VAT', $order)->item(0)?->nodeValue;
            $paymentVAT = $xpath->query('PAYMENT/VAT', $order)->item(0)?->nodeValue;

            if ($document = $this->documentRepository->findOneBy([
                'orderNumber' => $orderNumber
            ])) {

            }

            $items = $xpath->query('ITEMS', $order)->item(0);
            foreach ($items as $item) {
                $title = $xpath->query('TITLE', $item)->item(0)?->nodeValue;
                $code = $xpath->query('CODE', $item)->item(0)?->nodeValue;
                $ean = $xpath->query('EAN', $item)->item(0)?->nodeValue;
                $productId = $xpath->query('PRODUCT_ID', $item)->item(0)?->nodeValue;
                $quantity = $xpath->query('QUANTITY', $item)->item(0)?->nodeValue;
                $priceWithVat = $xpath->query('PRICE_WITH_VAT', $item)->item(0)?->nodeValue;
                $vat = $xpath->query('VAT', $item)->item(0)?->nodeValue;
            }
        }
    }

    /**
     * @param string $xmlFilePath
     * @param string $xslFilePath
     * @param Feed $feed
     * @return bool
     */
    private function parserFeed(string $xmlFilePath, string $xslFilePath, Feed $feed): bool
    {
        $command = "cd " . Feed::DIR_FEED_OUTPUT . "; java -jar " . Feed::DIR_FEED_SAXON .
            " -s:" . escapeshellarg($xmlFilePath) .
            " -xsl:" . escapeshellarg($xslFilePath) .
            " -o:" . escapeshellarg(Feed::DIR_FEED_OUTPUT . $feed->getOutputName()) .
            ' outputFileName="file://' . Feed::DIR_FEED_OUTPUT . $feed->getOutputName() . '"' .
            "; echo OK";

        $output = shell_exec($command);

        return trim($output) === "OK";
    }
}