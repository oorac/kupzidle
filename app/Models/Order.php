<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Attributes\EntityUpdatedOn;
    use App\Models\Interfaces\IEntity;
    use DateTime;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\OrderRepository")
     * @ORM\Table(name="order_list")
     * @ORM\HasLifecycleCallbacks
     */
    class Order implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;
        use EntityUpdatedOn;

        public const CENTER_ESHOP_ID = '464d6864-17cd-43ed-a38b-b0b6c0f58dd8';
        public const DELIVERY_METHOD_SK = 'NASED 1';
        public const DELIVERY_METHOD_CZ = 'NASED F';
        public const DELIVERY_METHOD_HERE = 'VASED';
        public const DELIVERY_METHOD_CODE_HERE = 'OSOSBNE';

        public const LIST_MALL_SHIPMENT_CODE = [
           'FOFR' => 'NASED',
           'GLS' => 'DOGLS',
           'DPD' => 'DOPR DPD',
           '123' => 'DOP123',
        ];

        public const DEFAULT_MALL_SHIPMENT_CODE = 'P';
        public const DEFAULT_MALL_SHIPMENT_CODE_FOR_ITEM = 'DOPRAVA_UNI';

        public const LIST_MALL_PAYMENT_CODE = [
            'převodem' => 'B',
            'předem' => 'B'
        ];

        public const DEFAULT_MALL_PAYMENT_CODE = 'H';
        public const DEFAULT_MALL_PAYMENT_CODE_FOR_ITEM = 'PLATBA_UNI';

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $orderNumber;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $status;

        /**
         * @var Currency
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Currency", inversedBy="orders")
         * @ORM\JoinColumn(name="currency", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Currency $currency;

        /**
         * @var OrderGroup
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\OrderGroup", inversedBy="orders")
         * @ORM\JoinColumn(name="order_group", referencedColumnName="id", onDelete="CASCADE")
         */
        protected OrderGroup $orderGroup;

        /**
         * @var DateTime|null
         *
         * @ORM\Column(type="date", nullable=true)
         */
        protected ?DateTime $date = null;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $variableSymbol;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $weight = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $totalPriceWithVatBeforeRounding = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $totalRoundingWithPrice = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $totalPriceWithVat = 0;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $url = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $externalOrderNumber = null;

        /**
         * @var DateTime|null
         *
         * @ORM\Column(type="datetime", nullable=true)
         */
        protected ?DateTime $paidDate = null;

        /**
         * @var bool
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $pricesWithVatYn = false;

        /**
         * @var bool
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $resolvedYn = false;

        /**
         * @var bool
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $ossYn = false;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $internalNote = null;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $trackingCode = null;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $trackingUrl = null;

        /**
         * @var Customer
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Customer", inversedBy="orders")
         * @ORM\JoinColumn(name="customer", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Customer $customer;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $shipmentTitle = null;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $shipmentCode = null;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $shipmentPrice = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $shipmentVat = 0;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $paymentTitle = null;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $paymentCode = null;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $paymentPrice = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $paymentVat = 0;

        /**
         * @var bool
         *
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $mall = false;

        /**
         * @var bool
         *
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $reverse = false;

        /**
         * @var DateTime|null
         *
         * @ORM\Column(type="datetime", nullable=true)
         */
        protected ?DateTime $syncDate = null;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\OrderProduct", mappedBy="order")
         */
        protected Collection $orderProducts;

        public function __construct()
        {
            $this->orderProducts = new ArrayCollection();
        }

        /**
         * @return string
         */
        public function getOrderNumber(): string
        {
            return $this->orderNumber;
        }

        /**
         * @param string $orderNumber
         * @return Order
         */
        public function setOrderNumber(string $orderNumber): Order
        {
            $this->orderNumber = $orderNumber;
            return $this;
        }

        /**
         * @return string
         */
        public function getStatus(): string
        {
            return $this->status;
        }

        /**
         * @param string $status
         * @return Order
         */
        public function setStatus(string $status): Order
        {
            $this->status = $status;
            return $this;
        }

        /**
         * @return Currency
         */
        public function getCurrency(): Currency
        {
            return $this->currency;
        }

        /**
         * @param Currency $currency
         * @return Order
         */
        public function setCurrency(Currency $currency): Order
        {
            $this->currency = $currency;
            return $this;
        }

        /**
         * @return OrderGroup
         */
        public function getOrderGroup(): OrderGroup
        {
            return $this->orderGroup;
        }

        /**
         * @param OrderGroup $orderGroup
         * @return Order
         */
        public function setOrderGroup(OrderGroup $orderGroup): Order
        {
            $this->orderGroup = $orderGroup;
            return $this;
        }

        /**
         * @return DateTime|null
         */
        public function getDate(): ?DateTime
        {
            return $this->date;
        }

        /**
         * @param DateTime|null $date
         * @return Order
         */
        public function setDate(?DateTime $date): Order
        {
            $this->date = $date;
            return $this;
        }

        /**
         * @return string
         */
        public function getVariableSymbol(): string
        {
            return $this->variableSymbol;
        }

        /**
         * @param string $variableSymbol
         * @return Order
         */
        public function setVariableSymbol(string $variableSymbol): Order
        {
            $this->variableSymbol = $variableSymbol;
            return $this;
        }

        /**
         * @return float
         */
        public function getWeight(): float
        {
            return $this->weight;
        }

        /**
         * @param float $weight
         * @return Order
         */
        public function setWeight(float $weight): Order
        {
            $this->weight = $weight;
            return $this;
        }

        /**
         * @return float
         */
        public function getTotalPriceWithVatBeforeRounding(): float
        {
            return $this->totalPriceWithVatBeforeRounding;
        }

        /**
         * @param float $totalPriceWithVatBeforeRounding
         * @return Order
         */
        public function setTotalPriceWithVatBeforeRounding(float $totalPriceWithVatBeforeRounding): Order
        {
            $this->totalPriceWithVatBeforeRounding = $totalPriceWithVatBeforeRounding;
            return $this;
        }

        /**
         * @return float
         */
        public function getTotalRoundingWithPrice(): float
        {
            return $this->totalRoundingWithPrice;
        }

        /**
         * @param float $totalRoundingWithPrice
         * @return Order
         */
        public function setTotalRoundingWithPrice(float $totalRoundingWithPrice): Order
        {
            $this->totalRoundingWithPrice = $totalRoundingWithPrice;
            return $this;
        }

        /**
         * @return float
         */
        public function getTotalPriceWithVat(): float
        {
            return $this->totalPriceWithVat;
        }

        /**
         * @param float $totalPriceWithVat
         * @return Order
         */
        public function setTotalPriceWithVat(float $totalPriceWithVat): Order
        {
            $this->totalPriceWithVat = $totalPriceWithVat;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getUrl(): ?string
        {
            return $this->url;
        }

        /**
         * @param string|null $url
         * @return Order
         */
        public function setUrl(?string $url): Order
        {
            $this->url = $url;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getExternalOrderNumber(): ?string
        {
            return $this->externalOrderNumber;
        }

        /**
         * @param string|null $externalOrderNumber
         * @return Order
         */
        public function setExternalOrderNumber(?string $externalOrderNumber): Order
        {
            $this->externalOrderNumber = $externalOrderNumber;
            return $this;
        }

        /**
         * @return DateTime|null
         */
        public function getPaidDate(): ?DateTime
        {
            return $this->paidDate;
        }

        /**
         * @param DateTime|null $paidDate
         * @return Order
         */
        public function setPaidDate(?DateTime $paidDate): Order
        {
            $this->paidDate = $paidDate;
            return $this;
        }

        /**
         * @return bool
         */
        public function isPricesWithVatYn(): bool
        {
            return $this->pricesWithVatYn;
        }

        /**
         * @param bool $pricesWithVatYn
         * @return Order
         */
        public function setPricesWithVatYn(bool $pricesWithVatYn): Order
        {
            $this->pricesWithVatYn = $pricesWithVatYn;
            return $this;
        }

        /**
         * @return bool
         */
        public function isResolvedYn(): bool
        {
            return $this->resolvedYn;
        }

        /**
         * @param bool $resolvedYn
         * @return Order
         */
        public function setResolvedYn(bool $resolvedYn): Order
        {
            $this->resolvedYn = $resolvedYn;
            return $this;
        }

        /**
         * @return bool
         */
        public function isOssYn(): bool
        {
            return $this->ossYn;
        }

        /**
         * @param bool $ossYn
         * @return Order
         */
        public function setOssYn(bool $ossYn): Order
        {
            $this->ossYn = $ossYn;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getInternalNote(): ?string
        {
            return $this->internalNote;
        }

        /**
         * @param string|null $internalNote
         * @return Order
         */
        public function setInternalNote(?string $internalNote): Order
        {
            $this->internalNote = $internalNote;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getTrackingCode(): ?string
        {
            return $this->trackingCode;
        }

        /**
         * @param string|null $trackingCode
         * @return Order
         */
        public function setTrackingCode(?string $trackingCode): Order
        {
            $this->trackingCode = $trackingCode;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getTrackingUrl(): ?string
        {
            return $this->trackingUrl;
        }

        /**
         * @param string|null $trackingUrl
         * @return Order
         */
        public function setTrackingUrl(?string $trackingUrl): Order
        {
            $this->trackingUrl = $trackingUrl;
            return $this;
        }

        /**
         * @return Customer
         */
        public function getCustomer(): Customer
        {
            return $this->customer;
        }

        /**
         * @param Customer $customer
         * @return Order
         */
        public function setCustomer(Customer $customer): Order
        {
            $this->customer = $customer;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getShipmentTitle(): ?string
        {
            return $this->shipmentTitle;
        }

        /**
         * @param string|null $shipmentTitle
         * @return Order
         */
        public function setShipmentTitle(?string $shipmentTitle): Order
        {
            $this->shipmentTitle = $shipmentTitle;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getShipmentCode(): ?string
        {
            return $this->shipmentCode;
        }

        /**
         * @param string|null $shipmentCode
         * @return Order
         */
        public function setShipmentCode(?string $shipmentCode): Order
        {
            $this->shipmentCode = $shipmentCode;
            return $this;
        }

        /**
         * @return float
         */
        public function getShipmentPrice(): float
        {
            return $this->shipmentPrice;
        }

        /**
         * @param float $shipmentPrice
         * @return Order
         */
        public function setShipmentPrice(float $shipmentPrice): Order
        {
            $this->shipmentPrice = $shipmentPrice;
            return $this;
        }

        /**
         * @return float
         */
        public function getShipmentVat(): float
        {
            return $this->shipmentVat;
        }

        /**
         * @param float $shipmentVat
         * @return Order
         */
        public function setShipmentVat(float $shipmentVat): Order
        {
            $this->shipmentVat = $shipmentVat;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getPaymentTitle(): ?string
        {
            return $this->paymentTitle;
        }

        /**
         * @param string|null $paymentTitle
         * @return Order
         */
        public function setPaymentTitle(?string $paymentTitle): Order
        {
            $this->paymentTitle = $paymentTitle;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getPaymentCode(): ?string
        {
            return $this->paymentCode;
        }

        /**
         * @param string|null $paymentCode
         * @return Order
         */
        public function setPaymentCode(?string $paymentCode): Order
        {
            $this->paymentCode = $paymentCode;
            return $this;
        }

        /**
         * @return float
         */
        public function getPaymentPrice(): float
        {
            return $this->paymentPrice;
        }

        /**
         * @param float $paymentPrice
         * @return Order
         */
        public function setPaymentPrice(float $paymentPrice): Order
        {
            $this->paymentPrice = $paymentPrice;
            return $this;
        }

        /**
         * @return float
         */
        public function getPaymentVat(): float
        {
            return $this->paymentVat;
        }

        /**
         * @param float $paymentVat
         * @return Order
         */
        public function setPaymentVat(float $paymentVat): Order
        {
            $this->paymentVat = $paymentVat;
            return $this;
        }

        /**
         * @return DateTime|null
         */
        public function getSyncDate(): ?DateTime
        {
            return $this->syncDate;
        }

        /**
         * @param DateTime|null $syncDate
         * @return Order
         */
        public function setSyncDate(?DateTime $syncDate): Order
        {
            $this->syncDate = $syncDate;
            return $this;
        }

        /**
         * @return bool
         */
        public function isMall(): bool
        {
            return $this->mall;
        }

        /**
         * @param bool $mall
         * @return Order
         */
        public function setMall(bool $mall): Order
        {
            $this->mall = $mall;
            return $this;
        }

        /**
         * @return bool
         */
        public function isReverse(): bool
        {
            return $this->reverse;
        }

        /**
         * @param bool $reverse
         * @return Order
         */
        public function setReverse(bool $reverse): Order
        {
            $this->reverse = $reverse;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getOrderProducts(): Collection
        {
            return $this->orderProducts;
        }

        /**
         * @param Collection $orderProducts
         * @return Order
         */
        public function setOrderProducts(Collection $orderProducts): Order
        {
            $this->orderProducts = $orderProducts;
            return $this;
        }
    }