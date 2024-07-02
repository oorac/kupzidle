<?php declare(strict_types = 1);

namespace App\Services\UpGates;

use App\Models\Address;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Meta;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductMeta;
use App\Models\Repositories\AddressRepository;
use App\Models\Repositories\CategoryRepository;
use App\Models\Repositories\CurrencyRepository;
use App\Models\Repositories\CustomerRepository;
use App\Models\Repositories\LabelRepository;
use App\Models\Repositories\MetaRepository;
use App\Models\Repositories\OrderGroupRepository;
use App\Models\Repositories\OrderProductRepository;
use App\Models\Repositories\OrderRepository;
use App\Models\Repositories\ProductCategoryRepository;
use App\Models\Repositories\ProductMetaRepository;
use App\Models\Repositories\ProductParameterRepository;
use App\Models\Repositories\ProductRepository;
use App\Models\Repositories\StoreRepository;
use App\Models\Repositories\SupplierOrderRepository;
use App\Models\Store;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderProduct;
use App\Services\Doctrine\EntityManager;
use App\Services\ProductService;
use app\Services\RabbitMQ\AMQService;
use App\Utils\Random;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Tracy\ILogger;

class UpGatesService
{
    /**
     * @var UpGatesClient
     * @inject
     */
    public UpGatesClient $upGatesClient;

    /**
     * @var EntityManager
     * @inject
     */
    public EntityManager $entityManager;

    /**
     * @var AddressRepository
     * @inject
     */
    public AddressRepository $addressRepository;

    /**
     * @var ProductRepository
     * @inject
     */
    public ProductRepository $productRepository;

    /**
     * @var CategoryRepository
     * @inject
     */
    public CategoryRepository $categoryRepository;

    /**
     * @var ProductCategoryRepository
     * @inject
     */
    public ProductCategoryRepository $productCategoryRepository;

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
     * @var OrderRepository
     * @inject
     */
    public OrderRepository $orderRepository;

    /**
     * @var CurrencyRepository
     * @inject
     */
    public CurrencyRepository $currencyRepository;

    /**
     * @var OrderProductRepository
     * @inject
     */
    public OrderProductRepository $orderProductRepository;

    /**
     * @var AMQService
     * @inject
     */
    public AMQService $rabbitMQService;

    /**
     * @var ILogger
     * @inject
     */
    public ILogger $logger;

    /**
     * @var ProductService
     * @inject
     */
    public ProductService $productService;

    /**
     * @var OrderGroupRepository
     * @inject
     */
    public OrderGroupRepository $orderGroupRepository;

    /**
     * @var CustomerRepository
     * @inject
     */
    public CustomerRepository $customerRepository;

    /**
     * @var StoreRepository
     * @inject
     */
    public StoreRepository $storeRepository;

    /**
     * @var SupplierOrderRepository
     * @inject
     */
    public SupplierOrderRepository $supplierOrderRepository;

    /**
     * @param UpGatesClient $upGatesClient
     * @param EntityManager $entityManager
     * @param AddressRepository $addressRepository
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param ProductCategoryRepository $productCategoryRepository
     * @param ProductMetaRepository $productMetaRepository
     * @param MetaRepository $metaRepository
     * @param ProductParameterRepository $productParameterRepository
     * @param LabelRepository $labelRepository
     * @param OrderRepository $orderRepository
     * @param CurrencyRepository $currencyRepository
     * @param OrderProductRepository $orderProductRepository
     * @param AMQService $rabbitMQService
     * @param ILogger $logger
     * @param ProductService $productService
     * @param OrderGroupRepository $orderGroupRepository
     * @param CustomerRepository $customerRepository
     * @param StoreRepository $storeRepository
     * @param SupplierOrderRepository $supplierOrderRepository
     */
    public function __construct(
        UpGatesClient $upGatesClient,
        EntityManager $entityManager,
        AddressRepository $addressRepository,
        ProductRepository          $productRepository,
        CategoryRepository         $categoryRepository,
        ProductCategoryRepository  $productCategoryRepository,
        ProductMetaRepository      $productMetaRepository,
        MetaRepository             $metaRepository,
        ProductParameterRepository $productParameterRepository,
        LabelRepository            $labelRepository,
        OrderRepository            $orderRepository,
        CurrencyRepository         $currencyRepository,
        OrderProductRepository     $orderProductRepository,
        AMQService                 $rabbitMQService,
        ILogger                    $logger,
        ProductService             $productService,
        OrderGroupRepository       $orderGroupRepository,
        CustomerRepository         $customerRepository,
        StoreRepository            $storeRepository,
        SupplierOrderRepository    $supplierOrderRepository
    )
    {
        $this->upGatesClient = $upGatesClient;
        $this->entityManager = $entityManager;
        $this->addressRepository = $addressRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productMetaRepository = $productMetaRepository;
        $this->metaRepository = $metaRepository;
        $this->productParameterRepository = $productParameterRepository;
        $this->labelRepository = $labelRepository;
        $this->orderRepository = $orderRepository;
        $this->currencyRepository = $currencyRepository;
        $this->orderProductRepository = $orderProductRepository;
        $this->rabbitMQService = $rabbitMQService;
        $this->logger = $logger;
        $this->productService = $productService;
        $this->orderGroupRepository = $orderGroupRepository;
        $this->customerRepository = $customerRepository;
        $this->storeRepository = $storeRepository;
        $this->supplierOrderRepository = $supplierOrderRepository;
    }

    /**
     * @param string $productId
     * @return void
     */
    final public function getProduct(string $productId): void
    {
        $response = $this->upGatesClient->getProduct($productId);
        $a = 1;
    }

    /**
     * @param bool $all
     * @return void
     * @throws Exception
     */
    final public function getProducts(bool $all = false): void
    {
        $response = $this->upGatesClient->getProducts(1, $all);

        foreach ($response['products'] as $product) {
            if ($product['code'] === null) {
                continue;
            }

            $this->rabbitMQService->sendMessage('saveProduct', json_encode(['data' => $product], JSON_THROW_ON_ERROR));
        }

        if ($response['number_of_pages'] !== 1) {
            for ($i = 2; $i <= $response['number_of_pages']; $i++) {
                $response = $this->upGatesClient->getProducts($i, $all);

                foreach ($response['products'] as $product) {
                    if ($product['code'] === null) {
                        continue;
                    }

                    $this->rabbitMQService->sendMessage('saveProduct', json_encode(['data' => $product], JSON_THROW_ON_ERROR));
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param bool $all
     * @return void
     * @throws Exception
     */
    final public function getParameters(bool $all = false): void
    {
        $response = $this->upGatesClient->getProductParameters(1, $all);

        foreach ($response['products'] as $product) {
            $this->rabbitMQService->sendMessage('saveParameter', json_encode(['data' => $product], JSON_THROW_ON_ERROR));
        }

        if (isset($response['number_of_pages']) && $response['number_of_pages'] !== 1) {
            for ($i = 2; $i <= $response['number_of_pages']; $i++) {
                $response = $this->upGatesClient->getProductParameters($i, $all);

                if (! isset($response['products'])) {
                    $this->logger->log(json_encode($response, JSON_THROW_ON_ERROR), ILogger::ERROR);
                    break;
                }
                foreach ($response['products'] as $product) {
                    $this->rabbitMQService->sendMessage('saveParameter', json_encode(['data' => $product], JSON_THROW_ON_ERROR));
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param bool $all
     * @return void
     */
    final public function getLabels(bool $all = false): void
    {
        $response = $this->upGatesClient->getProductLabels(1, $all);

        foreach ($response['products'] as $product) {
            if (! $this->productRepository->findOneBy([
                'productCode' => $product['code']
            ])) {
                continue;
            }
            $this->rabbitMQService->sendMessage('saveLabel', json_encode(['data' => $product], JSON_THROW_ON_ERROR));
        }

        if ($response['number_of_pages'] !== 1) {
            for ($i = 2; $i <= $response['number_of_pages']; $i++) {
                $response = $this->upGatesClient->getProductLabels($i, $all);

                foreach ($response['products'] as $product) {
                    if (! $this->productRepository->findOneBy([
                        'productCode' => $product['code']
                    ])) {
                        continue;
                    }
                    $this->rabbitMQService->sendMessage('saveLabel', json_encode(['data' => $product], JSON_THROW_ON_ERROR));
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @return void
     * @throws Exception
     */
    final public function getCategories(): void
    {
        $response = $this->upGatesClient->getCategories();

        foreach ($response['categories'] as $category) {
            $this->rabbitMQService->sendMessage('saveCategory', json_encode(['data' => $category], JSON_THROW_ON_ERROR));
        }

        if ($response['number_of_pages'] !== 1) {
            for ($i = 2; $i <= $response['number_of_pages']; $i++) {
                $response = $this->upGatesClient->getCategories($i);

                foreach ($response['categories'] as $category) {
                    $this->rabbitMQService->sendMessage('saveCategory', json_encode(['data' => $category], JSON_THROW_ON_ERROR));
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param int $categoryId
     * @return array
     */
    public function getCategoryByCategoryId(int $categoryId): array
    {
        return $this->upGatesClient->getCategoryByCategoryId($categoryId);
    }

    /**
     * @param string $number
     * @return array
     */
    public function getOrderByNumber(string $number): array
    {
        return $this->upGatesClient->getOrderByNumber($number);
    }

    /**
     * @return void
     * @throws Exception
     */
    final public function getOrders(): void
    {
        $response = $this->upGatesClient->getOrders();

        foreach ($response['orders'] as $order) {
            $this->rabbitMQService->sendMessage('saveOrder', json_encode(['data' => $order], JSON_THROW_ON_ERROR));
        }

        if ($response['number_of_pages'] !== 1) {
            for ($i = 2; $i <= $response['number_of_pages']; $i++) {
                $response = $this->upGatesClient->getOrders($i);

                foreach ($response['orders'] as $order) {
                    $this->rabbitMQService->sendMessage('saveOrder', json_encode(['data' => $order], JSON_THROW_ON_ERROR));
                }
            }
        }
    }

    /**
     * @param array $orderData
     * @return void
     */
    public function processOrder(array $orderData): void
    {
        $orderNumber = $orderData['order_number'];
        $prefix = 'ES';
        $minValue = 272417684;
        if (str_starts_with($orderNumber, $prefix)) {
            $numberPart = substr($orderNumber, strlen($prefix));
            if (is_numeric($numberPart) && intval($numberPart) <= $minValue) {
                return;
            }
        } else {
            return;
        }

        if (! $orderEntity = $this->orderRepository->findOneBy([
            'orderNumber' => $orderData['order_number']
        ])) {
            $orderEntity = (new Order())
                ->setOrderNumber($orderData['order_number'])
                ->setSyncDate(null);
            $this->entityManager->persist($orderEntity);
        }

        // Nechceme aktualizaci objednávek v systému
        if ($orderEntity->getSyncDate() !== null) {
            return;
        }

        $currencyId = strtoupper($orderData['currency_id']);
        $currency = $this->getCurrency($currencyId);
        $customer = $orderData['customer'];
        $countryCode = $customer['country_id_postal'] ?? $customer['country_id_invoice'];
        $groupId = ($currencyId === Currency::DEFAULT_CURRENCY) ? OrderGroup::CZ_GROUP_ID : OrderGroup::SK_GROUP_ID;

        $orderGroup = $this->orderGroupRepository->findOneBy([
            'type' => OrderGroup::TYPE_SUBSCRIBER,
            'groupId' => $groupId
        ]);

        if (! $orderGroup) {
            $this->logger->log(
                new Exception('Neexistující skupina pro měnu ' . $currencyId),
                ILogger::ERROR
            );
            return;
        }

        if (
            ($customer['ico'] !== null
                || $customer['dic'] !== null)
            && strtoupper($countryCode) === Address::COUNTRY_CODE_SK
            && $orderGroupCZDIC = $this->orderGroupRepository->findOneBy([
                'groupId' => OrderGroup::CZ_DIC_EUR,
                'type' => OrderGroup::TYPE_SUBSCRIBER
            ])
        ) {
            $orderGroup = $orderGroupCZDIC;
            $orderEntity->setReverse(true);
        }

        $shipmentCode = $this->getShipmentCode($orderData['shipment']);
        $paymentCode = $this->getPaymentCode($orderData['payment']);
        $mallOrderGroup = null;

        if (
            $shipmentCode === Order::DEFAULT_MALL_SHIPMENT_CODE
            && $paymentCode === Order::DEFAULT_MALL_PAYMENT_CODE
        ) {
            $mallOrderGroup = $this->orderGroupRepository->findOneBy([
                'groupId' => OrderGroup::MALL_GROUP_ID
            ]);

            $orderEntity->setMall(true);
        }

        $orderEntity->setOrderGroup($mallOrderGroup ?? $orderGroup);

        $customerEshopId = $customer['customer_id'];
        $customerEntity = $this->getCustomer((string) $customerEshopId);

        $invoiceAddress = $this->getAddress($orderData);

        if ($invoiceAddress === null) {
            $this->logger->log(
                new Exception('Neexistuje fakturační adresa pro zákazníka s id: ' . $customerEshopId),
                ILogger::ERROR
            );
            return;
        }

        $deliveryAddress = $this->getAddress($orderData, 'postal');

        if ($deliveryAddress === null) {
            $deliveryAddress = $invoiceAddress;
        }

        $customerEntity->setInvoiceAddress($invoiceAddress)
            ->setDeliveryAddress($deliveryAddress);

        $orderEntity = $this->setOrderData($orderEntity, $orderData, $currency, $customerEntity, $paymentCode, $shipmentCode);

        $resultProducts = [
            'products' => [],
            'createSupplierOrder' => false
        ];

        $this->saveProducts($orderData['products'], $orderEntity, $resultProducts);

        if (isset($orderData['discount_voucher']['discounts'])) {
            foreach ($orderData['discount_voucher']['discounts'] as $voucher) {
                $this->processVoucher($voucher, $orderData['discount_voucher'], $orderEntity);
            }
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($orderEntity);

        if ($resultProducts['createSupplierOrder'] === true) {
            $this->createSupplierOrder($orderEntity, $resultProducts);
        }
    }

    /**
     * @param array $products
     * @param Order $orderEntity
     * @param array $resultProducts
     * @return void
     */
    private function saveProducts(array $products, Order $orderEntity, array &$resultProducts): void
    {
        foreach ($products as $product) {
            $result = $this->processProduct($product, $orderEntity);
            $resultProducts['products'][$product['code']] = $result;

            if ($resultProducts['createSupplierOrder'] === false && ! empty($result) && $result['createSupplierOrder'] === true) {
                $resultProducts['createSupplierOrder'] = $result['createSupplierOrder'];
            }
        }
    }

    /**
     * @param string|null $customerEshopId
     * @return Customer
     */
    private function getCustomer(?string $customerEshopId = null): Customer
    {
        if ($customerEshopId === null) {
            $customerEshopId = sprintf('MALL-%s', Random::generate(15, '0-9'));

            $customerEntity = (new Customer())
                ->setCompanyEshopId($customerEshopId);
            $this->entityManager->persist($customerEntity);
        } elseif (! $customerEntity = $this->customerRepository->findOneBy([
            'companyEshopId' => $customerEshopId
        ])) {
            $customerEntity = (new Customer())
                ->setCompanyEshopId($customerEshopId);
            $this->entityManager->persist($customerEntity);
        }

        return $customerEntity;
    }

    /**
     * @param array $shipment
     * @return string|null
     */
    private function getShipmentCode(array $shipment): ?string
    {
        $shipmentCode = null;
        if (empty($shipment['code'])) {
            foreach (Order::LIST_MALL_SHIPMENT_CODE as $title => $code) {
                if (str_contains($shipment['name'], $title)) {
                    $shipmentCode = $code;
                    break;
                }
            }
            return $shipmentCode ?? Order::DEFAULT_MALL_SHIPMENT_CODE;
        }


        return $shipment['code'];
    }

    /**
     * @param array $payment
     * @return string|null
     */
    private function getPaymentCode(array $payment): ?string
    {
        $paymentCode = null;
        if (empty($payment['code'])) {
            foreach (Order::LIST_MALL_PAYMENT_CODE as $title => $code) {
                if (str_contains($payment['name'], $title)) {
                    $paymentCode = $code;
                    break;
                }
            }

            return $paymentCode ?? Order::DEFAULT_MALL_PAYMENT_CODE;
        }

        return $payment['code'];
    }

    /**
     * @param Order $orderEntity
     * @param array $orderData
     * @param Currency $currencyEntity
     * @param Customer $customerEntity
     * @param string|null $paymentCode
     * @param string|null $shipmentCode
     * @return Order
     */
    private function setOrderData(
        Order $orderEntity,
        array $orderData,
        Currency $currencyEntity,
        Customer $customerEntity,
        ?string $paymentCode = null,
        ?string $shipmentCode = null
    ): Order
    {
        $orderEntity->setUrl($orderData['admin_url'])
            ->setDate(new DateTime($orderData['creation_time']))
            ->setStatus($orderData['status'])
            ->setWeight($orderData['total_weight'])
            ->setCurrency($currencyEntity)
            ->setVariableSymbol($orderData['variable_symbol'])
            ->setInternalNote($orderData['customer']['customer_note'])
            ->setCustomer($customerEntity)
            ->setExternalOrderNumber($orderData['external_order_number'])
            ->setOssYn((bool) $orderData['oss_yn'])
            ->setPaidDate($orderData['paid_date'] !== null ? new DateTime() : null)
            ->setPaymentTitle($orderData['payment']['name'])
            ->setPaymentCode($paymentCode)
            ->setPaymentPrice((float) $orderData['payment']['price'])
            ->setPaymentVat((float) $orderData['payment']['vat'])
            ->setPricesWithVatYn((bool) $orderData['prices_with_vat_yn'])
            ->setResolvedYn($orderData['resolved_yn'])
            ->setShipmentTitle($orderData['shipment']['name'])
            ->setShipmentCode($shipmentCode)
            ->setShipmentPrice((float) $orderData['shipment']['price'])
            ->setShipmentVat((float) $orderData['shipment']['vat'])
            ->setTotalPriceWithVat((float) $orderData['order_total'])
            ->setTotalPriceWithVatBeforeRounding((float) $orderData['order_total_before_round'])
            ->setTotalRoundingWithPrice((float) $orderData['order_total_rest'])
            ->setTrackingCode($orderData['tracking_code'])
            ->setTrackingUrl($orderData['tracking_url']);

        return $orderEntity;
    }

    /**
     * @param string $currencyId
     * @return Currency
     */
    private function getCurrency(string $currencyId): Currency
    {
        if (! $currency = $this->currencyRepository->findOneBy([
            'code' => $currencyId
        ])) {
            $currency = (new Currency())
                ->setCode($currencyId)
                ->setTitle($currencyId);
            $this->entityManager->persist($currency);
            $this->entityManager->flush();
        }

        return $currency;
    }

    /**
     * @param Order $orderEntity
     * @param array $resultProducts
     * @return void
     */
    private function createSupplierOrder(Order $orderEntity, array $resultProducts): void
    {
        $listOrderProduct = $this->orderProductRepository->findBy([
            'order' => $orderEntity
        ]);

        $listSupplier = $this->getListSupplier($listOrderProduct, $resultProducts);

        foreach ($listSupplier as $supplierId => $supplierData) {
            $this->processSaveSupplierOrder($supplierId, $orderEntity, $supplierData, $listOrderProduct);
        }
    }

    /**
     * @param string $supplierId
     * @param Order $orderEntity
     * @param array $supplierData
     * @param Collection $listOrderProduct
     * @return void
     */
    private function processSaveSupplierOrder(
        string $supplierId,
        Order $orderEntity,
        array $supplierData,
        Collection $listOrderProduct
    ): void
    {
        if (! $supplier = $this->customerRepository->findOneBy([
            'companyId' => $supplierId
        ])) {
            return;
        }

        // Nechceme aktualizovat dodavatelskou objednávku
        if ($this->supplierOrderRepository->findOneBy([
            'subscriber' => $orderEntity->getCustomer(),
            'variableSymbol' => $orderEntity->getVariableSymbol(),
            'customer' => $supplier,
            'date' => $orderEntity->getDate()
        ])) {
            return;
        }

        $deliveryAddress = $this->getDeliveryAddress($orderEntity, $supplier, $supplierData['deliveryAddress']);

        if (! $orderGroup = $this->orderGroupRepository->findOneBy([
            'currencyCode' => Currency::DEFAULT_CURRENCY,
            'type' => OrderGroup::TYPE_SUPPLIER
        ])) {
            $this->logger->log(
                new Exception('Neexistující skupina pro měnu ' . $orderEntity->getCurrency()->getCode()),
                ILogger::ERROR
            );
            return;
        }

        $customerEntity = $orderEntity->getCustomer();
        $deliveryTitle = $this->getDeliveryTitle($customerEntity, $orderEntity);
        $paymentTitle = $orderEntity->getPaymentCode();

        $supplierOrderEntity = $this->getSupplierOrder(
            $supplier,
            $orderEntity,
            $deliveryAddress,
            $orderGroup,
            $deliveryTitle,
            $paymentTitle
        );
        $supplierOrderEntity->addOrder($orderEntity);

        $totalPriceWithVat = 0;
        $this->saveSupplierProducts($totalPriceWithVat, $listOrderProduct, $supplierOrderEntity, $supplierData);
        $supplierOrderEntity->setTotalPriceWithVat($totalPriceWithVat);
        $this->entityManager->flush();
    }

    /**
     * @param Collection $listOrderProduct
     * @param array $resultProducts
     * @return array
     */
    private function getListSupplier(Collection $listOrderProduct, array $resultProducts): array
    {
        $listSupplier = [];

        /** @var OrderProduct $orderProduct */
        foreach ($listOrderProduct as $orderProduct) {
            if ($orderProduct->getProduct()->isVoucher()) {
                continue;
            }
            if (
                ! isset($resultProducts['products'][$orderProduct->getProduct()->getProductCode()])
                || $resultProducts['products'][$orderProduct->getProduct()->getProductCode()]['createSupplierOrder'] === false
            ) {
                continue;
            }
            $supplierId = $orderProduct->getProduct()->getSupplier()?->getCompanyId();
            $listSupplier[$supplierId]['products'][$orderProduct->getProduct()->getProductCode()] = $orderProduct;
            $listSupplier[$supplierId]['deliveryAddress'] = $resultProducts['products'][$orderProduct->getProduct()->getProductCode()]['ourAddress'];
        }

        return $listSupplier;
    }

    /**
     * @param float $totalPriceWithVat
     * @param Collection $listOrderProduct
     * @param SupplierOrder $supplierOrderEntity
     * @param array $supplierData
     * @return void
     */
    private function saveSupplierProducts(
        float &$totalPriceWithVat,
        Collection $listOrderProduct,
        SupplierOrder $supplierOrderEntity,
        array $supplierData
    ): void
    {
        /** @var OrderProduct $orderProductEntity */
        foreach ($listOrderProduct as $orderProductEntity) {
            if (! isset($supplierData['products'][$orderProductEntity->getProduct()->getProductCode()])) {
                continue;
            }
            if ($orderProductEntity->getStore() === null) {
                $this->logger->log("Product nemá vyplněný sklad: " . $orderProductEntity->getProduct()->getProductCode(), ILogger::DEBUG);
                $orderProductEntity->setStore($this->storeRepository->findOneBy([
                    'code' => Store::DEFAULT_BRNO_STORE_CODE
                ]));
            }
            $totalPriceWithVat += $orderProductEntity->getTotalPrice();
            $supplierOrderProductEntity = (new SupplierOrderProduct())
                ->setVat($orderProductEntity->getVat())
                ->setSupplierOrder($supplierOrderEntity)
                ->setProduct($orderProductEntity->getProduct())
                ->setQuantity($orderProductEntity->getQuantity())
                ->setPricePerUnit($orderProductEntity->getPricePerUnit())
                ->setPriceWithVat($orderProductEntity->getPriceWithVat())
                ->setPriceWithoutVat($orderProductEntity->getPriceWithoutVat())
                ->setTotalPrice($orderProductEntity->getTotalPrice())
                ->setPurchasePrice($orderProductEntity->getPurchasePrice())
                ->setStore($orderProductEntity->getStore())
                ->setInternalNote($orderProductEntity->getInternalNote());

            $this->entityManager->persist($supplierOrderProductEntity);
            $orderProductEntity->setSupplierOrderProduct($supplierOrderProductEntity);
        }
    }

    /**
     * @param Customer $supplier
     * @param Order $orderEntity
     * @param Address $deliveryAddress
     * @param OrderGroup $orderGroup
     * @param string $deliveryTitle
     * @param string $paymentTitle
     * @return SupplierOrder
     */
    private function getSupplierOrder(
        Customer $supplier,
        Order $orderEntity,
        Address $deliveryAddress,
        OrderGroup $orderGroup,
        string $deliveryTitle,
        string $paymentTitle
    ): SupplierOrder
    {
        $supplierOrderEntity = (new SupplierOrder())
            ->setOrderGroup($orderGroup)
            ->setCustomer($supplier)
            ->setDate($orderEntity->getDate())
            ->setDeliveryAddress($deliveryAddress)
            ->setSubscriber($orderEntity->getCustomer())
            ->setCurrency($orderEntity->getCurrency())
            ->setVariableSymbol($orderEntity->getVariableSymbol())
            ->setTitle(sprintf(SupplierOrder::DEFAULT_TITLE, $orderEntity->getOrderNumber()))
            ->setShipmentTitle($deliveryTitle)
            ->setPaymentTitle($paymentTitle)
            ->setInternalNote($orderEntity->getInternalNote());

        $this->entityManager->persist($supplierOrderEntity);

        return $supplierOrderEntity;
    }

    /**
     * @param Customer $customerEntity
     * @param Order $orderEntity
     * @return string
     */
    private function getDeliveryTitle(Customer $customerEntity, Order $orderEntity): string
    {
        if ($customerEntity->getCompanyId() === Address::ADDRESS_ANTARES_ID) {
            if ($orderEntity->getShipmentCode() === Order::DELIVERY_METHOD_CODE_HERE) {
                return Order::DELIVERY_METHOD_HERE;
            }
            
            if (strtoupper($customerEntity->getDeliveryAddress()->getCountryCode()) === Address::COUNTRY_CODE_CZ) {
                return Order::DELIVERY_METHOD_CZ;
            }

            return Order::DELIVERY_METHOD_SK;
        }

        return $orderEntity->getShipmentCode();
    }

    /**
     * @param Order $orderEntity
     * @param Customer $supplier
     * @param bool $ourAddress
     * @return Address
     */
    private function getDeliveryAddress(Order $orderEntity, Customer $supplier, bool $ourAddress = false): Address
    {
        if ($ourAddress === false) {
            return $supplier->getDeliveryAddress();
        }

        return $orderEntity->getCustomer()->getDeliveryAddress();
    }

//    /**
//     * @param Customer $supplier
//     * @param OrderProduct $orderProductEntity
//     * @return Store
//     */
//    private function getStoreEntityBySupplierCondition(Customer $supplier, OrderProduct $orderProductEntity): Store
//    {
//        $supplier->getConditions()
//    }

    /**
     * @param array $orderData
     * @param string $type
     * @return Address|null
     */
    private function getAddress(array $orderData, string $type = 'invoice'): ?Address
    {
        $prefix = $type === 'invoice' ? 'invoice' : 'postal';
        $title = sprintf('%s %s', $orderData['customer']['firstname_' . $prefix], $orderData['customer']['surname_' . $prefix]);
        $companyName = (bool) $orderData['customer']['company_yn'] ? $orderData['customer']['company'] : $title;

        if (
            ($type !== 'invoice') && (empty($orderData['customer']['city_' . $prefix]) || $orderData['customer']['city_' . $prefix] === null)
            && (empty($orderData['customer']['street_' . $prefix]) || $orderData['customer']['street_' . $prefix] === null)
        ) {
            return null;
        }

        $address = $this->addressRepository->findOneBy([
            'phone' => $orderData['customer']['phone'],
            'email' => $orderData['customer']['email'],
            'city' => $orderData['customer']['city_' . $prefix],
            'companyName' => $companyName,
            'street' => $orderData['customer']['street_' . $prefix],
            'zipCode' => $orderData['customer']['zip_' . $prefix],
            'countryCode' => $orderData['customer']['country_id_' . $prefix],
            'contactName' => $title,
            'crnId' => $orderData['customer']['ico'],
            'vatId' => $orderData['customer']['dic']
        ]);

        if (! $address) {
            $address = (new Address())
                ->setType(Address::TYPE_ADDRESS_SUBSCRIBER)
                ->setPhone($orderData['customer']['phone'])
                ->setEmail($orderData['customer']['email'])
                ->setTitle($title)
                ->setCity($orderData['customer']['city_' . $prefix])
                ->setCompanyName($companyName)
                ->setStreet($orderData['customer']['street_' . $prefix])
                ->setZipCode($orderData['customer']['zip_' . $prefix])
                ->setCountryCode($orderData['customer']['country_id_' . $prefix] ?? Address::COUNTRY_CODE_CZ)
                ->setContactName($title)
                ->setCrnId($orderData['customer']['ico'])
                ->setVatId($orderData['customer']['dic']);

            $this->entityManager->persist($address);
        }

        return $address;
    }

    /**
     * @param array $productData
     * @param Order $orderEntity
     * @param bool $reverse
     * @return array|false[]|true[]
     */
    private function processProduct(array $productData, Order $orderEntity, bool $reverse = false): array
    {
        // TODO dodělat detekci na reverse charge a mít typ ceny jen základ a sazba nenulová.
        $result = [];

        try {
            if (! $productEntity = $this->productRepository->findOneBy([
                'productCode' => $productData['code']
            ])) {
                $response = $this->upGatesClient->getProduct((string) $productData['product_id']);

                $productNew = $response['products'][0];
                $productEntity = $this->productService->saveProduct($productNew);
            }

            if (! $productEntity) {
                $this->logger->log(sprintf('Neexistuje product s %s', $productData['product_id']),
                    ILogger::WARNING
                );
                return [];
            }

            if (! $orderProductEntity = $this->orderProductRepository->findOneBy([
                'order' => $orderEntity,
                'product' => $productEntity
            ])) {
                $orderProductEntity = (new OrderProduct())
                    ->setProduct($productEntity)
                    ->setOrder($orderEntity);
                $this->entityManager->persist($orderProductEntity);
            }

            $configurationText = '';
            if (isset($productData['parameters'])) {
                foreach ($productData['parameters'] as $parameter) {
                    if (empty($configurationText)) {
                        $configurationText = sprintf('- %s: %s', $parameter['name'], $parameter['value']);
                    } else {
                        $configurationText = "\r\n" . sprintf('- %s: %s', $parameter['name'], $parameter['value']);
                    }
                }
            }

            if (isset($productData['configurations'])) {
                foreach ($productData['configurations'] as $configuration) {
                    $configurationValueText = '';
                    foreach ($configuration['values'] as $value) {
                        if (empty($configurationValueText) && (float) $value['price'] !== 0.0) {
                            $configurationValueText = sprintf('%s (+ %d %s)', $value['value'], $value['price'], $orderEntity->getCurrency()->getCode());
                        } else if (empty($configurationValueText)) {
                            $configurationValueText = sprintf('%s', $value['value']);
                        } else if (! empty($configurationValueText) && (float) $value['price'] !== 0.0) {
                            $configurationValueText .= sprintf(', %s (+ %d %s)', $value['value'], $value['price'], $orderEntity->getCurrency()->getCode());
                        } else {
                            $configurationValueText .= sprintf(', %s', $value['value']);
                        }
                    }

                    if (empty($configurationText)) {
                        $configurationText = sprintf('- %s: %s', $configuration['name'], $configurationValueText);
                    } else {
                        $configurationText .= "\r\n" . sprintf('- %s: %s', $configuration['name'], $configurationValueText);
                    }
                }
            }

            $orderProductEntity->setVat((float)($productData['vat'] ?? 0))
                ->setPurchasePrice((float)($productData['buy_price'] ?? 0))
                ->setQuantity((float)($productData['quantity'] ?? 0))
                ->setTotalPrice((float)($productData['price'] ?? 0))
                ->setPriceWithVat((float)($productData['price_with_vat'] ?? 0))
                ->setPriceWithoutVat((float)($productData['price_without_vat'] ?? 0))
                ->setAvailability($productData['availability'])
                ->setPricePerUnit((float)($productData['price_per_unit'] ?? 0))
                ->setRecycleFee((float)($productData['recycling_fee'] ?? 0))
                ->setInternalNote($configurationText);

            $productMetas = $this->productRepository->findProductMetasWithSpecificCodes($orderProductEntity->getProduct());

            $storeEntity = null;

            /** @var ProductMeta $productMeta */
            foreach ($productMetas as $productMeta) {
                if ($storeEntity !== null) {
                    break;
                }

                if ($productMeta->getMeta()->getCode() === Meta::SK_BRNO
                    && (float) $productMeta->getValue() >= $orderProductEntity->getQuantity()
                ) {
                    $storeEntity = $this->storeRepository->findOneBy([
                        'code' => Store::DEFAULT_BRNO_STORE_CODE
                    ]);
                    $productMeta->setValue((string) ($productMeta->getValue() - $orderProductEntity->getQuantity()));
//                    $this->logger->log($orderProductEntity->getProduct()->getProductCode() . ' :brno', ILogger::DEBUG);
                    break;
                }

                if ($productMeta->getMeta()->getCode() === Meta::SK_MOSS
                    && (float) $productMeta->getValue() >= $orderProductEntity->getQuantity()
                ) {
                    $storeEntity = $this->storeRepository->findOneBy([
                        'code' => Store::DEFAULT_MOSS_STORE_CODE
                    ]);
                    $productMeta->setValue((string) ($productMeta->getValue() - $orderProductEntity->getQuantity()));
//                    $this->logger->log($orderProductEntity->getProduct()->getProductCode() . ' :moss', ILogger::DEBUG);
                    break;
                }
            }

            if (
                $storeEntity === null
                && ($meta = $this->metaRepository->findOneBy([
                    'code' => Meta::SK_SUPPLIER
                ]))
                && $productMeta = $this->productMetaRepository->findOneBy([
                    'product' => $orderProductEntity->getProduct(),
                    'meta' => $meta
                ])
            ) {
                $storeEntity = $this->storeRepository->findOneBy([
                    'code' => Store::DEFAULT_SUPPLIER_STORE_CODE
                ]);
                $newQuantity = (float) $productMeta->getValue() - $orderProductEntity->getQuantity();
                $productMeta->setValue((string) $newQuantity);
            }

            if ($storeEntity === null) {
                $this->logger->log("Sklad nebyl nalezen: " . $orderProductEntity->getProduct()->getProductCode(), ILogger::DEBUG);
                $storeEntity = $this->storeRepository->findOneBy([
                    'code' => Store::DEFAULT_BRNO_STORE_CODE
                ]);
                $orderProductEntity->setStore($storeEntity);

                return [
                    'createSupplierOrder' => false,
                    'ourAddress' => false
                ];
            }

            if (in_array($storeEntity->getCode(), [Store::DEFAULT_BRNO_STORE_CODE, Store::DEFAULT_MOSS_STORE_CODE], true)) {
                $orderProductEntity->setStore($storeEntity);

                return [
                    'createSupplierOrder' => false,
                    'ourAddress' => false
                ];
            }

            if ($orderProductEntity->getProduct()->getSupplier()?->getCompanyId() === Address::ADDRESS_HOUSE_LIFE_ID) {
//                $this->logger->log(
//                    new Exception(sprintf('Houselife produkt %s není k dispozici na MOSS ani na SK2', $orderProductEntity->getProduct()->getProductCode())),
//                    ILogger::CRITICAL
//                );

                return [
                    'createSupplierOrder' => false,
                    'ourAddress' => false
                ];
            }

            $supplierEntity = $orderProductEntity->getProduct()->getSupplier();
            if ($supplierEntity === null) {
                $orderProductEntity->setStore($storeEntity);
                return [
                    'createSupplierOrder' => false,
                    'ourAddress' => false
                ];
            }

            $result = $this->searchStore($supplierEntity, $orderProductEntity, $storeEntity);
            $orderProductEntity->setStore($result['storeEntity']);
        } catch (Exception $exception) {
            $this->logger->log(
                new Exception('Error processing product: ' . $exception->getMessage()),
                ILogger::ERROR
            );
        }

        return $result;
    }

    /**
     * @param array $voucherData
     * @param array $discount
     * @param Order $orderEntity
     * @param bool $reverse
     * @return void
     */
    private function processVoucher(array $voucherData, array $discount, Order $orderEntity, bool $reverse = false): void
    {
        // TODO dodělat detekci na reverse charge a mít typ ceny jen základ a sazba nenulová.
        try {
            if (! $productEntity = $this->productRepository->findOneBy([
                'productCode' => $discount['code']
            ])) {
                $productEntity = (new Product())
                    ->setTitle(Product::DEFAULT_VOUCHER_TITLE)
                    ->setProductCode($discount['code'])
                    ->setVoucher(true);
                $this->entityManager->persist($productEntity);
            }

            if (! $orderProductEntity = $this->orderProductRepository->findOneBy([
                'order' => $orderEntity,
                'product' => $productEntity
            ])) {
                $orderProductEntity = (new OrderProduct())
                    ->setProduct($productEntity)
                    ->setOrder($orderEntity);
                $this->entityManager->persist($orderProductEntity);
            }

            $orderProductEntity->setVat((float)($voucherData['vat'] ?? 0))
                ->setQuantity(1)
                ->setTotalPrice((float)($voucherData['price'] ?? 0))
                ->setPricePerUnit((float)($voucherData['price'] ?? 0));

        } catch (Exception $exception) {
            $this->logger->log(
                new Exception('Error processing voucher: ' . $exception->getMessage()),
                ILogger::ERROR
            );
        }
    }

    /**
     * @param Customer $supplierEntity
     * @param OrderProduct $orderProductEntity
     * @param Store $supplierStoreEntity
     * @return array|true[]
     */
    private function searchStore(Customer $supplierEntity, OrderProduct $orderProductEntity, Store $supplierStoreEntity): array
    {
        if ($supplierEntity->getCompanyId() === Address::ADDRESS_IDEA_NABYTEK_ID) {
            if (! $storeEntity = $this->storeRepository->findOneBy([
                'code' => Store::DEFAULT_BRNO_STORE_CODE
            ])) {
                //TODO exception
            }
            return [
                'storeEntity' => $storeEntity,
                'createSupplierOrder' => true,
                'ourAddress' => true
            ];
        }

        if (
            in_array($supplierEntity->getCompanyId(), [
                Address::ADDRESS_OFFICE_PRO_ID,
                Address::ADDRESS_OFFICE_MORE_ID,
                Address::ADDRESS_ANTARES_ID,
                Address::ADDRESS_ALBA_ID,
                Address::ADDRESS_KAVING_SIT_ID,
                Address::ADDRESS_ITTC_STIMA_ID,
                Address::ADDRESS_BRADOP_ID,
                Address::ADDRESS_FIBER_MOUNTS_ID,
                Address::ADDRESS_FLOKK_ID,
                Address::ADDRESS_SEGO_ID,
                Address::ADDRESS_RIM_CZ_ID,
                Address::ADDRESS_LAMA_PLUS_ID,
                Address::ADDRESS_ADK_TRADE_ID,
                Address::ADDRESS_AUTRONIC_ID,
                Address::ADDRESS_FASTJUMP_ID,
                Address::ADDRESS_DESIGNOVE_ZIDLE_ID,
                Address::ADDRESS_HON_ID,
                Address::ADDRESS_AXIN_TRADING_ID
            ], true)
        ) {
            if ($orderProductEntity->getOrder()->getShipmentCode() === 'OSOSBNE') {
                if (! $storeEntity = $this->storeRepository->findOneBy([
                    'code' => Store::DEFAULT_BRNO_STORE_CODE
                ])) {
                    //TODO exception
                }

                return [
                    'storeEntity' => $storeEntity,
                    'createSupplierOrder' => true,
                    'ourAddress' => true
                ];
            }

            return [
                'storeEntity' => $supplierStoreEntity,
                'createSupplierOrder' => true,
                'ourAddress' => false
            ];
        }

        if (
            in_array($supplierEntity->getCompanyId(), [
                Address::ADDRESS_LD_SEATING_ID,
                Address::ADDRESS_PROWORK_ID,
                Address::ADDRESS_MAYER_ID,
            ], true)
        ) {

            return [
                'storeEntity' => $supplierStoreEntity,
                'createSupplierOrder' => true,
                'ourAddress' => false
            ];
        }

        if ($supplierEntity->getCompanyId() === Address::ADDRESS_TEMPO_KONDELA_ID) {
            if (! $storeEntity = $this->storeRepository->findOneBy([
                'code' => Store::DEFAULT_BRNO_STORE_CODE
            ])) {
                //TODO exception
            }


            return [
                'storeEntity' => $storeEntity,
                'createSupplierOrder' => true,
                'ourAddress' => true
            ];
        }

        if (in_array($supplierEntity->getCompanyId(), [Address::ADDRESS_HALMAR_ID, Address::ADDRESS_SIGNAL_ID], true)) {
            if (! $storeEntity = $this->storeRepository->findOneBy([
                'code' => Store::DEFAULT_BRNO_STORE_CODE
            ])) {
                //TODO exception
            }

            return [
                'storeEntity' => $storeEntity,
                'createSupplierOrder' => true,
                'ourAddress' => true
            ];
        }

        if (in_array($supplierEntity->getCompanyId(), [Address::ADDRESS_BIBL_ID, Address::ADDRESS_ROJAPLAST_ID], true)) {
            if (! $storeEntity = $this->storeRepository->findOneBy([
                'code' => Store::DEFAULT_BRNO_STORE_CODE
            ])) {
                //TODO exception
            }

            $words = [
                'ORTA',
                'ANVIL',
                'MUSKA',
                'PRINCE',
                'ZAZI',
                'ESME',
                'TALI',
                'JUNGLE',
                'SELUNA',
                'SANTOS',
                'LAZIO',
                'KAMALA',
                'KRAKA',
                'QUEEN',
                'MOLTES',
                'DIALGO',
                'COMO',
            ];

            $title = $orderProductEntity->getProduct()->getTitle() ?? $orderProductEntity->getProduct()->getParent()?->getTitle();
            foreach ($words as $word) {
                if (str_contains($title, $word)) {
                    if ($storeBrnoEntity = $this->storeRepository->findOneBy([
                        'code' => Store::DEFAULT_BRNO_STORE_CODE
                    ])) {
                        return [
                            'storeEntity' => $storeBrnoEntity,
                            'createSupplierOrder' => true,
                            'ourAddress' => false
                        ];
                    }
                    return [
                        'storeEntity' => $supplierStoreEntity,
                        'createSupplierOrder' => true,
                        'ourAddress' => false
                    ];
                }
            }

            return [
                'storeEntity' => $storeEntity,
                'createSupplierOrder' => true,
                'ourAddress' => true
            ];
        }

        return [
            'storeEntity' => $supplierStoreEntity,
            'createSupplierOrder' => false,
            'ourAddress' => false
        ];
    }
}