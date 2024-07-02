<?php declare(strict_types = 1);

namespace App\Services\Money;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Repositories\AddressRepository;
use App\Models\Repositories\CustomerRepository;
use App\Models\Repositories\OrderProductRepository;
use App\Models\Repositories\OrderRepository;
use App\Models\Repositories\ProductRepository;
use App\Models\Repositories\StoreRepository;
use App\Models\Repositories\SupplierOrderProductRepository;
use App\Models\Repositories\SupplierOrderRepository;
use App\Models\Store;
use App\Models\SupplierOrder;
use App\Services\Doctrine\EntityManager;
use DateTime;
use DOMDocument;
use DOMElement;
use Ramsey\Uuid\Uuid;
use Tracy\ILogger;

class ExportOrderService
{
    private const EXPORT_ORDER_DIR = DIR_WWW . DS . 'money/export/order';
    public const BACKUP_EXPORT_DIR = self::EXPORT_ORDER_DIR . DS . 'backup';
    private const EXTENSION_FILE = '.xml';

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
     * @var StoreRepository
     * @inject
     */
    public StoreRepository $storeRepository;

    /**
     * @var OrderRepository
     * @inject
     */
    public OrderRepository $orderRepository;

    /**
     * @var OrderProductRepository
     * @inject
     */
    public OrderProductRepository $orderProductRepository;

    /**
     * @var AddressRepository
     * @inject
     */
    public AddressRepository $addressRepository;

    /**
     * @var CustomerRepository
     * @inject
     */
    public CustomerRepository $customerRepository;

    /**
     * @var SupplierOrderProductRepository
     * @inject
     */
    public SupplierOrderProductRepository $supplierOrderProductRepository;

    /**
     * @var SupplierOrderRepository
     * @inject
     */
    public SupplierOrderRepository $supplierOrderRepository;

    /**
     * @var ILogger
     * @inject
     */
    public ILogger $logger;

    /**
     * @param ProductRepository $productRepository
     * @param EntityManager $entityManager
     * @param StoreRepository $storeRepository
     * @param OrderRepository $orderRepository
     * @param OrderProductRepository $orderProductRepository
     * @param AddressRepository $addressRepository
     * @param CustomerRepository $customerRepository
     * @param SupplierOrderProductRepository $supplierOrderProductRepository
     * @param SupplierOrderRepository $supplierOrderRepository
     * @param ILogger $logger
     */
    public function __construct(
        ProductRepository $productRepository,
        EntityManager $entityManager,
        StoreRepository $storeRepository,
        OrderRepository $orderRepository,
        OrderProductRepository $orderProductRepository,
        AddressRepository $addressRepository,
        CustomerRepository $customerRepository,
        SupplierOrderProductRepository $supplierOrderProductRepository,
        SupplierOrderRepository $supplierOrderRepository,
        ILogger $logger
    )
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
        $this->storeRepository = $storeRepository;
        $this->orderRepository = $orderRepository;
        $this->orderProductRepository = $orderProductRepository;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->supplierOrderProductRepository = $supplierOrderProductRepository;
        $this->supplierOrderRepository = $supplierOrderRepository;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function createXmlFile(): void
    {
        try {
            $orderEntityList = $this->orderRepository->findBy([
                'syncDate' => null
            ]);

            /** @var Order $orderEntity */
            foreach ($orderEntityList as $orderEntity) {
                $dom = new DOMDocument();
                $dom->encoding = 'utf-8';
                $dom->xmlVersion = '1.0';
                $dom->formatOutput = true;

                $root = $this->createAndAppendElement($dom, $dom,'S5Data');

                $companyList = $this->createAndAppendElement($dom, $root,'FirmaList');
                $company = $this->createAndAppendElement($dom, $companyList,'Firma');
                $dom = $this->createCompanyHeader($dom, $company, $orderEntity);
                $dom = $this->createContactList($dom, $company, $orderEntity);

                $orderList = $this->createAndAppendElement($dom, $root,'ObjednavkaPrijataList');
                $order = $this->createAndAppendElement($dom, $orderList,'ObjednavkaPrijata');
                $dom = $this->createHeader($dom, $order, $orderEntity);
                $dom = $this->createAddress($dom, $order, $orderEntity);
                $dom = $this->createItems($dom, $order, $orderEntity);

                $orderEntity->setSyncDate(new DateTime());
                $this->saveFile($dom, 'subscriber/ORDERS_SUBSCRIBER_' . $orderEntity->getVariableSymbol() . '_' . (new DateTime())->format('d_m_Y'));
                $this->entityManager->flush();
            }
        } catch (\Exception $exception) {
            $this->logger->log($exception, ILogger::CRITICAL);
        }
    }

    /**
     * @return void
     */
    public function createOrderSupplier(): void
    {
        try {
            $listSupplierOrder = $this->supplierOrderRepository->findBy([
                'syncDate' => null
            ]);

            /** @var SupplierOrder $supplierOrderEntity */
            foreach ($listSupplierOrder as $supplierOrderEntity) {
                $dom = new DOMDocument();
                $dom->encoding = 'utf-8';
                $dom->xmlVersion = '1.0';
                $dom->formatOutput = true;

                $root = $this->createAndAppendElement($dom, $dom,'S5Data');

                $orderList = $this->createAndAppendElement($dom, $root,'ObjednavkaVydanaList');

                $order = $this->createAndAppendElement($dom, $orderList,'ObjednavkaVydana');
                $dom = $this->createSupplierHeader($dom, $order, $supplierOrderEntity);
                $dom = $this->createSupplierAddress($dom, $order, $supplierOrderEntity);
                $dom = $this->createSupplierItems($dom, $order, $supplierOrderEntity);
                $supplierOrderEntity->setSyncDate(new DateTime());
                $this->saveFile($dom, 'supplier/ORDERS_SUPPLIER_' . $supplierOrderEntity->getVariableSymbol() . '_' . (new DateTime())->format('d_m_Y'));
                $this->entityManager->flush();
            }
        } catch (\Exception $exception) {
            $this->logger->log($exception, ILogger::CRITICAL);
        }
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $company
     * @param Order $orderEntity
     * @return DOMDocument
     */
    private function createContactList(DOMDocument $dom, DOMElement $company, Order $orderEntity): DOMDocument
    {
        $guid = Uuid::uuid4()->toString();
        $addressEntity = $orderEntity->getCustomer()->getInvoiceAddress();
        $listPerson = $this->createAndAppendElement($dom, $company, 'Osoby');
        $person = $this->createAndAppendElement($dom, $listPerson, 'SeznamOsob');
        $onePerson = $this->createAndAppendElement($dom, $person, 'Osoba');
        $onePerson->setAttribute('ID', $guid);
        $personEntity = explode(' ', $addressEntity->getContactName());
        $this->createAndAppendElement($dom, $onePerson, 'Jmeno', $personEntity[0]);
        $this->createAndAppendElement($dom, $onePerson, 'Prijmeni', $personEntity[1]);
        $this->createAndAppendElement($dom, $onePerson, 'Nazev', $addressEntity->getContactName());
        $this->createAndAppendElement($dom, $onePerson, 'CisloOsoby', '1');

        $listContact = $this->createAndAppendElement($dom, $company, 'SeznamSpojeni');
        $contactPhone = $this->createAndAppendElement($dom, $listContact, 'Spojeni');
        $this->createAndAppendElement($dom, $contactPhone, 'Poradi', '1');
        $this->createAndAppendElement($dom, $contactPhone, 'SpojeniCislo', ltrim(trim($addressEntity->getPhone())));

        $type = $this->createAndAppendElement($dom, $contactPhone, 'TypSpojeni');
        $this->createAndAppendElement($dom, $type, 'Kod', 'Tel');

        $this->createAndAppendElement($dom, $contactPhone, 'Osoba_ID', $guid);

        $contactEmail = $this->createAndAppendElement($dom, $listContact, 'Spojeni');
        $this->createAndAppendElement($dom, $contactEmail, 'Poradi', '2');
        $this->createAndAppendElement($dom, $contactEmail, 'SpojeniCislo', ltrim(trim($addressEntity->getEmail())));

        $type = $this->createAndAppendElement($dom, $contactEmail, 'TypSpojeni');
        $this->createAndAppendElement($dom, $type, 'Kod', 'E-mail');

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $company
     * @param Order $orderEntity
     * @return DOMDocument
     */
    private function createCompanyHeader(DOMDocument $dom, DOMElement $company, Order $orderEntity): DOMDocument
    {
        $customerEntity = $orderEntity->getCustomer();
        $addressEntity = $customerEntity->getInvoiceAddress();
        if (! empty($addressEntity->getCrnId())) {
            $this->createAndAppendElement($dom, $company, 'ICO', $addressEntity->getCrnId());
        }
        if (! empty($addressEntity->getVatId())) {
            $this->createAndAppendElement($dom, $company, 'DIC', $addressEntity->getVatId());
        }
        if (! empty($customerEntity->getCompanyEshopId())) {
            $this->createAndAppendElement($dom, $company, 'Kod', $customerEntity->getCompanyEshopId());
        }
        if (! empty($addressEntity->getCompanyName())) {
            $this->createAndAppendElement($dom, $company, 'Nazev', $addressEntity->getCompanyName());
        }

        $listAddress = $this->createAndAppendElement($dom, $company,'Adresy');
        $dom = $this->createCompanyAddress($dom, $listAddress, $orderEntity);
        return $this->createCompanyAddress($dom, $listAddress, $orderEntity, 'delivery');
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $listAddress
     * @param Order $orderEntity
     * @param string $type
     * @return DOMDocument
     */
    private function createCompanyAddress(DOMDocument $dom, DOMElement $listAddress, Order $orderEntity, string $type = 'invoice'): DOMDocument
    {
        $customerEntity = $orderEntity->getCustomer();
        if ($type === 'invoice') {
            $addressEntity = $customerEntity->getInvoiceAddress();
            $nameAddress = 'ObchodniAdresa';
        } else {
            $addressEntity = $customerEntity->getDeliveryAddress();
            $nameAddress = 'Provozovna';
            if (
                ($addressEntity->getStreet() === null || empty($addressEntity->getStreet()))
                && ($addressEntity->getCity() === null || empty($addressEntity->getCity()))
                && ($addressEntity->getZipCode() === null || empty($addressEntity->getZipCode()))
            ) {
                return $dom;
            }
            $this->createAndAppendElement($dom, $listAddress, 'OdlisnaAdresaProvozovny', 'True');
        }

        if (
            ($addressEntity->getStreet() === null || empty($addressEntity->getStreet()))
            && ($addressEntity->getCity() === null || empty($addressEntity->getCity()))
            && ($addressEntity->getZipCode() === null || empty($addressEntity->getZipCode()))
        ) {
            return $dom;
        }

        $address = $this->createAndAppendElement($dom, $listAddress, $nameAddress);

        if (! empty($addressEntity->getCompanyName())) {
            $this->createAndAppendElement($dom, $address, 'Nazev', $addressEntity->getCompanyName());
        }
        if (! empty($addressEntity->getStreet())) {
            $this->createAndAppendElement($dom, $address, 'Ulice', $addressEntity->getStreet());
        }
        if (! empty($addressEntity->getCompanyName())) {
            $this->createAndAppendElement($dom, $address, 'Misto', $addressEntity->getCity());
        }
        if (! empty($addressEntity->getCompanyName())) {
            $this->createAndAppendElement($dom, $address, 'KodPsc', $addressEntity->getZipCode());
        }
        if (! empty($addressEntity->getCompanyName())) {
            $state = $this->createAndAppendElement($dom, $address, 'Stat');
            $this->createAndAppendElement($dom, $state, 'Kod', $addressEntity->getCountryCode());
        }

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $order
     * @param SupplierOrder $supplierOrderEntity
     * @return DOMDocument
     */
    private function createSupplierHeader(DOMDocument $dom, DOMElement $order, SupplierOrder $supplierOrderEntity): DOMDocument
    {
        $this->createAndAppendElement($dom, $order,'CiselnaRada_ID', $supplierOrderEntity->getNumberSeries());
        $this->createAndAppendElement($dom, $order,'Nazev', $supplierOrderEntity->getTitle());
        $this->createAndAppendElement($dom, $order,'DatumVystaveni', $supplierOrderEntity->getDate()?->format('Y-m-d'));

        $currency = $this->createAndAppendElement($dom, $order,'Mena');
        $this->createAndAppendElement($dom, $currency,'Kod', $supplierOrderEntity->getCurrency()->getCode());

        $myCompany = $this->createAndAppendElement($dom, $order,'MojeFirma');
        $this->createAndAppendElement($dom, $myCompany,'DIC', $supplierOrderEntity->getOrderGroup()->getVatId());

//        $texts = $this->createAndAppendElement($dom, $order,'Texty');
//        $this->createAndAppendElement($dom, $texts,'ZaCenami', $supplierOrderEntity->getInternalNote());

        $customerEntity = $supplierOrderEntity->getCustomer();
        if ($customerEntity->getCompanyId() === Address::ADDRESS_ANTARES_ID) {
            if (strtoupper($customerEntity->getDeliveryAddress()->getCountryCode()) === Address::COUNTRY_CODE_CZ) {
                $deliveryMethod = $this->createAndAppendElement($dom, $order, 'ZpusobDopravy');
                if ($supplierOrderEntity->getShipmentTitle() === Order::DELIVERY_METHOD_CODE_HERE) {
                    $this->createAndAppendElement($dom, $deliveryMethod,'Kod', Order::DELIVERY_METHOD_HERE);
                } else {
                    $this->createAndAppendElement($dom, $deliveryMethod,'Kod', Order::DELIVERY_METHOD_CZ);
                }
            } else if ($supplierOrderEntity->getShipmentTitle() === Order::DELIVERY_METHOD_CODE_HERE) {
                $deliveryMethod = $this->createAndAppendElement($dom, $order, 'ZpusobDopravy');
                $this->createAndAppendElement($dom, $deliveryMethod,'Kod', Order::DELIVERY_METHOD_HERE);
            } else {
                $deliveryMethod = $this->createAndAppendElement($dom, $order, 'ZpusobDopravy');
                $this->createAndAppendElement($dom, $deliveryMethod,'Kod', Order::DELIVERY_METHOD_SK);
            }
        } else {
            $deliveryMethod = $this->createAndAppendElement($dom, $order, 'ZpusobDopravy');
            $this->createAndAppendElement($dom, $deliveryMethod,'Kod', $supplierOrderEntity->getShipmentTitle());
        }

//        $paymentMethod = $this->createAndAppendElement($dom, $order, 'ZpusobPlatby');
//        $this->createAndAppendElement($dom, $paymentMethod,'Kod', $supplierOrderEntity->getPaymentTitle());

        $this->createAndAppendElement($dom, $order, 'Stredisko_ID', $supplierOrderEntity->getCenterId());

        $group = $this->createAndAppendElement($dom, $order, 'Group');
        $group->setAttribute('ID', $supplierOrderEntity->getOrderGroup()->getGroupId());
        $group->setAttribute('Kod', $supplierOrderEntity->getOrderGroup()->getCode());

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $order
     * @param Order $orderEntity
     * @return DOMDocument
     */
    private function createHeader(DOMDocument $dom, DOMElement $order, Order $orderEntity): DOMDocument
    {
        $this->createAndAppendElement($dom, $order,'CisloDokladu', $orderEntity->getOrderNumber());
        $this->createAndAppendElement($dom, $order,'Odkaz', $orderEntity->getOrderNumber());
        $this->createAndAppendElement($dom, $order,'Nazev', sprintf('Objednávka z e-shopu č. %s', $orderEntity->getOrderNumber()));

        $numberPart = substr($orderEntity->getOrderNumber(), strlen('EX'));
        $this->createAndAppendElement($dom, $order,'VariabilniSymbol', $numberPart ?? $orderEntity->getOrderNumber());
        $this->createAndAppendElement($dom, $order,'DatumVystaveni', $orderEntity->getDate()?->format('Y-m-d'));

        $myCompany = $this->createAndAppendElement($dom, $order,'MojeFirma');
        $this->createAndAppendElement($dom, $myCompany,'DIC', $orderEntity->getOrderGroup()->getVatId());

        $texts = $this->createAndAppendElement($dom, $order,'Texty');
        $this->createAndAppendElement($dom, $texts,'ZaCenami', $orderEntity->getInternalNote());

        $currency = $this->createAndAppendElement($dom, $order,'Mena');
        $this->createAndAppendElement($dom, $currency,'Kod', $orderEntity->getCurrency()->getCode());

        $deliveryMethod = $this->createAndAppendElement($dom, $order, 'ZpusobDopravy');
        $this->createAndAppendElement($dom, $deliveryMethod,'Kod', $orderEntity->getShipmentCode());

        $paymentMethod = $this->createAndAppendElement($dom, $order, 'ZpusobPlatby');
        $this->createAndAppendElement($dom, $paymentMethod,'Kod', $orderEntity->getPaymentCode());

        $group = $this->createAndAppendElement($dom, $order, 'Group');
        $group->setAttribute('ID', $orderEntity->getOrderGroup()->getGroupId());
        $group->setAttribute('Kod', $orderEntity->getOrderGroup()->getCode());

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $order
     * @param Order $orderEntity
     * @return DOMDocument
     */
    private function createAddress(DOMDocument $dom, DOMElement $order, Order $orderEntity): DOMDocument
    {
        $customerEntity = $orderEntity->getCustomer();

        $address = $this->createAndAppendElement($dom, $order, 'Adresa');
        $company = $this->createAndAppendElement($dom, $address,'Firma');
        $this->createAndAppendElement($dom, $company,'Kod', $customerEntity->getCompanyEshopId());

        $address2 = $this->createAndAppendElement($dom, $order, 'AdresaPrijemceFaktury');
        $company2 = $this->createAndAppendElement($dom, $address2,'Firma');
        $this->createAndAppendElement($dom, $company2,'Kod', $customerEntity->getCompanyEshopId());

        $address3 = $this->createAndAppendElement($dom, $order, 'AdresaKoncovehoPrijemce');
        $company3 = $this->createAndAppendElement($dom, $address3,'Firma');
        $this->createAndAppendElement($dom, $company3,'Kod', $customerEntity->getCompanyEshopId());

        $deliveryAddress = $customerEntity->getDeliveryAddress();

        $crnId = $deliveryAddress->getCrnId();
        if ($crnId !== null) {
            $this->createAndAppendElement($dom, $address3,'ICO', $crnId);
        }

        $vatId = $deliveryAddress->getVatId();
        if ($vatId !== null) {
            $this->createAndAppendElement($dom, $address3,'DIC', $vatId);
        }

        $this->createAndAppendElement($dom, $address3,'Nazev', $deliveryAddress->getCompanyName());
        $this->createAndAppendElement($dom, $address3,'KontaktniOsobaNazev', $deliveryAddress->getTitle());
        $this->createAndAppendElement($dom, $address3,'Email', $deliveryAddress->getEmail());
        $this->createAndAppendElement($dom, $address3,'Telefon', $deliveryAddress->getPhone());
        $this->createAndAppendElement($dom, $address3,'Ulice', $deliveryAddress->getStreet());
        $this->createAndAppendElement($dom, $address3,'Misto', $deliveryAddress->getCity());
        $this->createAndAppendElement($dom, $address3,'PSC', $deliveryAddress->getZipCode());

        $country = $this->createAndAppendElement($dom, $address3,'AdresaStat');
        $this->createAndAppendElement($dom, $country,'Kod', $deliveryAddress->getCountryCode());

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $order
     * @param SupplierOrder $supplierOrderEntity
     * @return DOMDocument
     */
    private function createSupplierAddress(DOMDocument $dom, DOMElement $order, SupplierOrder $supplierOrderEntity): DOMDocument
    {
        $customerEntity = $supplierOrderEntity->getCustomer();
        $address = $this->createAndAppendElement($dom, $order, 'Adresa');
        $company = $this->createAndAppendElement($dom, $address,'Firma');
        $this->createAndAppendElement($dom, $company,'Kod', $customerEntity->getCompanyEshopId());

        $address = $this->createAndAppendElement($dom, $order, 'AdresaPrijemceFaktury');
        $company = $this->createAndAppendElement($dom, $address,'Firma');
        $this->createAndAppendElement($dom, $company,'Kod', $customerEntity->getCompanyEshopId());

        $address = $this->createAndAppendElement($dom, $order, 'AdresaKoncovehoPrijemce');
        $company = $this->createAndAppendElement($dom, $address,'Firma');
        $this->createAndAppendElement($dom, $company,'Kod', $supplierOrderEntity->getSubscriber()->getCompanyEshopId());

        $invoiceAddress = $supplierOrderEntity->getSubscriber()->getInvoiceAddress();
        $crnId = $invoiceAddress->getCrnId();
        if ($crnId !== null) {
            $this->createAndAppendElement($dom, $address,'ICO', $crnId);
        }

        $vatId = $invoiceAddress->getVatId();
        if ($vatId !== null) {
            $this->createAndAppendElement($dom, $address,'DIC', $vatId);
        }

        $this->createAndAppendElement($dom, $address,'Nazev', $invoiceAddress->getCompanyName());
        $this->createAndAppendElement($dom, $address,'KontaktniOsobaNazev', $invoiceAddress->getTitle());
        $this->createAndAppendElement($dom, $address,'Email', $invoiceAddress->getEmail());
        $this->createAndAppendElement($dom, $address,'Telefon', $invoiceAddress->getPhone());
        $this->createAndAppendElement($dom, $address,'Ulice', $invoiceAddress->getStreet());
        $this->createAndAppendElement($dom, $address,'Misto', $invoiceAddress->getCity());
        $this->createAndAppendElement($dom, $address,'PSC', $invoiceAddress->getZipCode());

        $country = $this->createAndAppendElement($dom, $address,'AdresaStat');
        $this->createAndAppendElement($dom, $country,'Kod', $invoiceAddress->getCountryCode());

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $order
     * @param Order $orderEntity
     * @return DOMDocument
     */
    private function createItems(DOMDocument $dom, DOMElement $order, Order $orderEntity): DOMDocument
    {
        $items = $this->createAndAppendElement($dom, $order,'Polozky');

        $orderProductList = $this->orderProductRepository->findBy([
            'order' => $orderEntity
        ]);


        /** @var OrderProduct $orderProductEntity */
        foreach ($orderProductList as $orderProductEntity) {
            $dom = $this->processProduct($dom, $items, $orderProductEntity, $orderEntity);
        }

        $dom = $this->processShipmentProduct($dom, $items, $orderEntity);
        $dom = $this->processPaymentProduct($dom, $items, $orderEntity);

        $this->entityManager->flush();

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $items
     * @param OrderProduct $orderProductEntity
     * @param Order $orderEntity
     * @return DOMDocument
     */
    private function processProduct(DOMDocument $dom, DOMElement $items, OrderProduct $orderProductEntity, Order $orderEntity): DOMDocument
    {
        $item = $this->createAndAppendElement($dom, $items,'PolozkaObjednavkyPrijate');
        $this->createAndAppendElement($dom, $item,'Nazev', $orderProductEntity->getProduct()->getTitle());
        $this->createAndAppendElement($dom, $item,'Poznamka', $orderProductEntity->getInternalNote());
        $this->createAndAppendElement($dom, $item,'Mnozstvi', $orderProductEntity->getQuantity());
        $this->createAndAppendElement($dom, $item,'TypCeny', $orderEntity->isPricesWithVatYn() === true ? 1 : 0);
        $this->createAndAppendElement($dom, $item,'JednCenaCM', $orderProductEntity->getPricePerUnit());

        $vatItem = $this->createAndAppendElement($dom, $item,'DPH');
        $this->createAndAppendElement($dom, $vatItem,'Sazba', $orderProductEntity->getVat());

        $this->createAndAppendElement($dom, $item,'TypObsahu', 1);

        $contentItem = $this->createAndAppendElement($dom, $item,'ObsahPolozky');
        $artiklItem = $this->createAndAppendElement($dom, $contentItem,'Artikl');

        if ($orderProductEntity->getProduct()->isVoucher()) {
            $this->createAndAppendElement($dom, $artiklItem,'CarovyKod', Product::DEFAULT_VOUCHER_CODE);
        } else {
            $this->createAndAppendElement($dom, $artiklItem,'CarovyKod', $orderProductEntity->getProduct()->getProductCode());
        }

        $storeItem = $this->createAndAppendElement($dom, $contentItem,'Sklad');
        if ($orderProductEntity->getProduct()->isVoucher()) {
            $this->createAndAppendElement($dom, $storeItem,'Kod', Store::DEFAULT_BRNO_STORE_CODE);
            $this->createAndAppendElement($dom, $contentItem,'Vratka', 'True');
        } else {
            $this->createAndAppendElement($dom, $storeItem,'Kod', $orderProductEntity->getStore()?->getCode() ?? Store::DEFAULT_BRNO_STORE_CODE);
        }

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $items
     * @param Order $orderEntity
     * @return DOMDocument
     */
    private function processShipmentProduct(DOMDocument $dom, DOMElement $items, Order $orderEntity): DOMDocument
    {
        $item = $this->createAndAppendElement($dom, $items,'PolozkaObjednavkyPrijate');
        $this->createAndAppendElement($dom, $item,'Nazev', $orderEntity->getShipmentTitle());
        $this->createAndAppendElement($dom, $item,'Mnozstvi', 1);
        $this->createAndAppendElement($dom, $item,'TypCeny', $orderEntity->isPricesWithVatYn() === true ? 1 : 0);
        $this->createAndAppendElement($dom, $item,'JednCenaCM', $orderEntity->getShipmentPrice());

        $vatItem = $this->createAndAppendElement($dom, $item,'DPH');
        $this->createAndAppendElement($dom, $vatItem,'Sazba', $orderEntity->getShipmentVat());

        $this->createAndAppendElement($dom, $item,'TypObsahu', 1);

        $contentItem = $this->createAndAppendElement($dom, $item,'ObsahPolozky');

        $artiklItem = $this->createAndAppendElement($dom, $contentItem,'Artikl');

        if ($orderEntity->isMall()) {
            $this->createAndAppendElement($dom, $artiklItem,'CarovyKod', Order::DEFAULT_MALL_SHIPMENT_CODE_FOR_ITEM);
        } else {
            $this->createAndAppendElement($dom, $artiklItem,'CarovyKod', $orderEntity->getShipmentCode());
        }

        $storeItem = $this->createAndAppendElement($dom, $contentItem,'Sklad');
        $this->createAndAppendElement($dom, $storeItem,'Kod', Store::DEFAULT_BRNO_STORE_CODE);

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $items
     * @param Order $orderEntity
     * @return DOMDocument
     */
    private function processPaymentProduct(DOMDocument $dom, DOMElement $items, Order $orderEntity): DOMDocument
    {
        $item = $this->createAndAppendElement($dom, $items,'PolozkaObjednavkyPrijate');
        $this->createAndAppendElement($dom, $item,'Nazev', $orderEntity->getPaymentTitle());
        $this->createAndAppendElement($dom, $item,'Mnozstvi', 1);
        $this->createAndAppendElement($dom, $item,'TypCeny', $orderEntity->isPricesWithVatYn() === true ? 1 : 0);
        $this->createAndAppendElement($dom, $item,'JednCenaCM', $orderEntity->getPaymentPrice());

        $vatItem = $this->createAndAppendElement($dom, $item,'DPH');
        $this->createAndAppendElement($dom, $vatItem,'Sazba', $orderEntity->getPaymentVat());

        $this->createAndAppendElement($dom, $item,'TypObsahu', 1);

        $contentItem = $this->createAndAppendElement($dom, $item,'ObsahPolozky');

        $artiklItem = $this->createAndAppendElement($dom, $contentItem,'Artikl');

        if ($orderEntity->isMall()) {
            $this->createAndAppendElement($dom, $artiklItem,'CarovyKod', Order::DEFAULT_MALL_PAYMENT_CODE_FOR_ITEM);
        } else {
            $this->createAndAppendElement($dom, $artiklItem,'CarovyKod', $orderEntity->getPaymentCode());
        }

        $storeItem = $this->createAndAppendElement($dom, $contentItem,'Sklad');
        $this->createAndAppendElement($dom, $storeItem,'Kod', Store::DEFAULT_BRNO_STORE_CODE);

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $order
     * @param SupplierOrder $supplierOrderEntity
     * @return DOMDocument
     */
    private function createSupplierItems(DOMDocument $dom, DOMElement $order, SupplierOrder $supplierOrderEntity): DOMDocument
    {
        $items = $this->createAndAppendElement($dom, $order,'Polozky');

        $listSupplierProduct = $this->supplierOrderProductRepository->findBy([
            'supplierOrder' => $supplierOrderEntity
        ]);

        /** @var SupplierOrder $orderProductEntity */
        foreach ($listSupplierProduct as $orderProductEntity) {
            $item = $this->createAndAppendElement($dom, $items,'PolozkaObjednavkyVydane');
            $this->createAndAppendElement($dom, $item,'Nazev', $orderProductEntity->getProduct()->getTitle());
            $this->createAndAppendElement($dom, $item,'Poznamka', $orderProductEntity->getInternalNote());
            $this->createAndAppendElement($dom, $item,'Mnozstvi', $orderProductEntity->getQuantity());
            $this->createAndAppendElement($dom, $item,'TypCeny', 0);
            $this->createAndAppendElement($dom, $item,'JednCenaCM', round($orderProductEntity->getPriceWithoutVat() / $orderProductEntity->getQuantity(),2));

            $vatItem = $this->createAndAppendElement($dom, $item,'DPH');
            $this->createAndAppendElement($dom, $vatItem,'Sazba', $orderProductEntity->getVat());

            $this->createAndAppendElement($dom, $item,'TypObsahu', 1);

            $contentItem = $this->createAndAppendElement($dom, $item,'ObsahPolozky');

            $artiklItem = $this->createAndAppendElement($dom, $contentItem,'Artikl');
            $this->createAndAppendElement($dom, $artiklItem,'CarovyKod', $orderProductEntity->getProduct()->getProductCode());

            $storeItem = $this->createAndAppendElement($dom, $contentItem,'Sklad');
            $this->createAndAppendElement($dom, $storeItem,'Kod', $orderProductEntity->getStore()->getCode());
        }

        $this->entityManager->flush();

        return $dom;
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
        return $this->getFilenamePath(self::EXPORT_ORDER_DIR, $fileName, self::EXTENSION_FILE);
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