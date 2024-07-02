<?php declare(strict_types = 1);

namespace App\Services\Money;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Meta;
use App\Models\ProductMeta;
use App\Models\Repositories\AddressRepository;
use App\Models\Repositories\CustomerRepository;
use App\Models\Repositories\MetaRepository;
use App\Models\Repositories\ProductMetaRepository;
use App\Models\Repositories\ProductParameterRepository;
use App\Models\Repositories\ProductRepository;
use App\Models\Repositories\StoreRepository;
use App\Services\Doctrine\EntityManager;
use App\Utils\FileSystem;
use DateTime;
use Doctrine\DBAL\Exception\ConnectionException;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use RuntimeException;
use Tracy\ILogger;
use XMLReader;
use XMLWriter;

class ProductImportService
{

    public const BACKUP_IMPORT_DIR = DIR_WWW . DS . 'money/import/product/backup/';
    public const IMPORT_PRODUCT_DIR = DIR_WWW . DS . 'money/import/product/';
    public const IMPORT_PRODUCT_DIR_SPLIT = DIR_WWW . DS . 'money/import/product/split/';
    public const IMPORT_PRODUCT_DIR_SPLIT_BACKUP = DIR_WWW . DS . 'money/import/product/split/backup/';
    public const EXTENSION_FILE = 'xml';

    /**
     * @var ProductRepository
     * @inject
     */
    public ProductRepository $productRepository;

    /**
     * @var ProductParameterRepository
     * @inject
     */
    public ProductParameterRepository $productParameterRepository;

    /**
     * @var EntityManager
     * @inject
     */
    public EntityManager $entityManager;

    /**
     * @var StoreRepository
     * @inject
     */
    public StoreRepository $storeRepository;

    /**
     * @var AddressRepository
     * @inject
     */
    public AddressRepository $addressRepository;

    /**
     * @var ILogger
     * @inject
     */
    public ILogger $logger;

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
     * @var CustomerRepository
     * @inject
     */
    public CustomerRepository $customerRepository;

    /**
     * @param ProductRepository $productRepository
     * @param EntityManager $entityManager
     * @param ProductParameterRepository $productParameterRepository
     * @param StoreRepository $storeRepository
     * @param AddressRepository $addressRepository
     * @param ILogger $logger
     * @param ProductMetaRepository $productMetaRepository
     * @param MetaRepository $metaRepository
     */
    public function __construct(
        ProductRepository $productRepository,
        EntityManager $entityManager,
        ProductParameterRepository $productParameterRepository,
        StoreRepository $storeRepository,
        AddressRepository $addressRepository,
        ILogger $logger,
        ProductMetaRepository $productMetaRepository,
        MetaRepository $metaRepository,
        CustomerRepository $customerRepository
    )
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
        $this->productParameterRepository = $productParameterRepository;
        $this->storeRepository = $storeRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
        $this->productMetaRepository = $productMetaRepository;
        $this->metaRepository = $metaRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return void
     */
    final public function importData(): void
    {
        foreach ($this->loadFiles() as $loadFile) {
            $pathFile = self::IMPORT_PRODUCT_DIR . $loadFile;
            if (FileSystem::isFile($pathFile) && pathinfo($pathFile, PATHINFO_EXTENSION) === self::EXTENSION_FILE) {
                $this->splitXML($pathFile, self::IMPORT_PRODUCT_DIR_SPLIT);
                FileSystem::delete($pathFile);
            }
        }
    }

    /**
     * @param string $file
     * @return void
     * @throws ConnectionException
     */
    final public function saveData(string $file): void
    {
        if (! FileSystem::isFile($file) || pathinfo($file, PATHINFO_EXTENSION) !== self::EXTENSION_FILE) {
            return;
        }

        $reader = new XMLReader();
        if (!$reader->open($file)) {
            $this->logger->log(
                new Exception("Failed to open XML file: $file")
            );
            return;
        }

        $count = 0;
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'Zasoba') {
                $node = new DOMDocument();
                $domNode = $node->importNode($reader->expand(), true);
                $node->appendChild($domNode);

                $xpath = new DOMXPath($node);
                $product = $xpath->query('//Zasoba')->item(0);
                if ($product) {
                    $this->processProduct($product, $xpath);
                    $count++;
                    if ($count === 100) {
                        $this->flushWithRetry();
                        $count = 0;
                    }
                }
            }
        }
        $this->flushWithRetry();
        $reader->close();
    }

    /**
     * @param int $retries
     * @return void
     * @throws ConnectionException
     */
    private function flushWithRetry(int $retries = 5): void
    {
        while ($retries > 0) {
            try {
                $this->entityManager->flush();
                break;
            } catch (ConnectionException $e) {
                $this->logger->log($e, ILogger::ERROR);
                $retries--;
                if ($retries === 0) {
                    throw $e;
                }
                sleep(1);
            }
        }
    }


    /**
     * @param DOMElement $product
     * @param DOMXPath $xpath
     * @return void
     * @throws ConnectionException
     */
    private function processProduct(DOMElement $product, DOMXPath $xpath): void
    {
        $artiklId = $xpath->query('Artikl_ID', $product)->item(0)?->nodeValue;
        $companyId = $xpath->query('Artikl/Dodavatele/HlavniDodavatel/Firma_ID', $product)->item(0)?->nodeValue;
        $companyName = $xpath->query('Artikl/Dodavatele/HlavniDodavatel/NazevFirmy', $product)->item(0)?->nodeValue;
        $ean = $xpath->query('Artikl/CarovyKod', $product)->item(0)?->nodeValue;
        $storeId = $xpath->query('Sklad_ID', $product)->item(0)?->nodeValue;
        $title = $xpath->query('Nazev', $product)->item(0)?->nodeValue;


        $periods = $xpath->query('Stavy/StavZasoby/Obdobi/Zacatek', $product);
        $latestDate = null;
        $latestQuantity = 0;
        foreach ($periods as $period) {
            $currentDate = new DateTime($period->nodeValue);
            if ($latestDate === null || $currentDate > $latestDate) {
                $latestDate = $currentDate;

                $latestQuantity = $xpath->query('ancestor::StavZasoby/Zustatek/Mnozstvi', $period)->item(0)?->nodeValue;
            }
        }

        $stock = $latestQuantity ?? '0';

        $customer = $this->customerRepository->findOneBy([
            'companyId' => $companyId
        ]);

        if ($companyName === null) {
            $this->logger->log('firma ID ' . $companyId . ' u artiklu ' . $artiklId, ILogger::DEBUG);
            return;
        }

        if (! $customer && $companyId !== null) {
            $customer = (new Customer())
                ->setCompanyId($companyId);
            $this->entityManager->persist($customer);

            $address = (new Address())
                ->setType(Address::TYPE_ADDRESS_SUPPLIER)
                ->setCompanyName($companyName);
            $this->entityManager->persist($address);
            $customer->setInvoiceAddress($address)
                ->setDeliveryAddress($address);
        } elseif ($customer) {
            $customer->getInvoiceAddress()?->setCompanyName($companyName);
        }

        if ($productEntity = $this->productRepository->findOneBy([
            'productCode' => $ean
        ])) {
            $productEntity->setSupplier($customer);
        } elseif ($productEntity = $this->productRepository->findOneBy([
            'articleId' => $artiklId
        ])) {
            $productEntity->setSupplier($customer);
        } else {
            $this->logger->log(
                sprintf('Neexistující artikl pod kódem: %s a artiklId: %s', $ean, $artiklId),
                ILogger::ERROR
            );
            return;
        }

        if (empty($artiklId)) {
            $this->logger->log(
                sprintf('Neexistující artikl pod kódem: %s', $ean),
                ILogger::ERROR
            );
        }

        $productEntity->setTitle($title)
            ->setArticleId($artiklId)
            ->isMoneySync();

        if ($storeId === null) {
            $this->logger->log(
                sprintf('Nevyplněný sklad u produktu: %s', $productEntity->getProductCode()),
                ILogger::ERROR
            );
            return;
        }

        if (! $storeEntity = $this->storeRepository->findOneBy([
            'storeId' => $storeId
        ])) {
            $this->logger->log(
                new Exception(sprintf('Neexistující sklad pod ID: %s', $storeId)),
                ILogger::ERROR
            );
            return;
        }

        $meta = match (strtoupper($storeEntity->getCode())) {
            Meta::SK_MOSS => $this->metaRepository->findOneBy(['code' => Meta::SK_MOSS]),
            Meta::SK_BRNO => $this->metaRepository->findOneBy(['code' => Meta::SK_BRNO]),
            default => null,
        };

        if ($meta === null) {
            $this->logger->log(
                sprintf('Nenalezen META atribut pro žádný sklad pro produkt %s', $productEntity->getProductCode()),
                ILogger::ERROR
            );
            return;
        }

        $productMeta = $this->productMetaRepository->findOneBy(['product' => $productEntity, 'meta' => $meta]) ?? (new ProductMeta())->setProduct($productEntity)->setMeta($meta);

        $productMeta->setValue($stock);

        $this->entityManager->persist($productMeta);
        $this->flushWithRetry();
    }

    /**
     * @return array
     */
    private function loadFiles(): array
    {
        return FileSystem::scanDir(self::IMPORT_PRODUCT_DIR);
    }

    /**
     * Rozdělí XML soubor na menší soubory podle elementů 'Zasoba'.
     *
     * @param string $sourceFile Cesta k zdrojovému XML souboru.
     * @param string $destDirectory Cílová složka pro rozdělené XML soubory.
     * @param int $maxItems Maximální počet položek v jednom výstupním souboru.
     * @return void
     * @throws RuntimeException Pokud dojde k chybě při čtení nebo zápisu souboru.
     */
    private function splitXML(string $sourceFile, string $destDirectory, int $maxItems = 100): void
    {
        $reader = new XMLReader();
        if (!$reader->open($sourceFile)) {
            throw new RuntimeException("Nelze otevřít zdrojový soubor: $sourceFile");
        }

        $doc = new DOMDocument();
        $xmlWriter = null;
        $counter = 0;
        $fileCounter = 1;

        try {
            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'Zasoba') {
                    $node = simplexml_import_dom($doc->importNode($reader->expand(), true));

                    if ($counter % $maxItems === 0) {
                        if ($xmlWriter) {
                            $xmlWriter->endElement();
                            $xmlWriter->endElement();
                            $xmlWriter->endDocument();
                            $xmlWriter->flush();
                        }

                        $xmlWriter = new XMLWriter();

                        if (!is_dir($destDirectory) && !mkdir($destDirectory, 0775, true) && !is_dir($destDirectory)) {
                            $this->logger->log(
                                new \RuntimeException(sprintf('Directory "%s" was not created', $destDirectory))
                            );
                        }

                        $fileName = $destDirectory . DS . 'split_' . $fileCounter . (new DateTime())->format('Y_m_d_H_i_s') . '.xml';
                        if (!$xmlWriter->openURI($fileName)) {
                            throw new RuntimeException("Nelze otevřít soubor pro zápis: $fileName");
                        }
                        $xmlWriter->startDocument('1.0', 'UTF-8');
                        $xmlWriter->startElement('S5Data');
                        $xmlWriter->startElement('ZasobaList');

                        $fileCounter++;
                    }

                    $xmlWriter->writeRaw($node->asXML());
                    $counter++;
                }
            }
        } finally {
            if ($xmlWriter) {
                $xmlWriter->endElement();
                $xmlWriter->endElement();
                $xmlWriter->endDocument();
                $xmlWriter->flush();
            }
            $reader->close();
        }
    }

}