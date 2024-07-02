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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\SupplierOrderRepository")
     * @ORM\Table(name="supplier_order_list")
     * @ORM\HasLifecycleCallbacks
     */
    class SupplierOrder implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;
        use EntityUpdatedOn;

        public const DEFAULT_NUMBER_SERIES = 'ccbed7d3-abb9-49eb-a2a0-63aa8ebe81ae';
        public const STATUS_DRAFT = 'DRAFT';
        public const STATUS_CREATED = 'CREATED';
        public const STATUS_DELETED = 'DELETED';
        public const DEFAULT_TITLE = 'Objednávka z e-shopu č. %s';

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $numberSeries = self::DEFAULT_NUMBER_SERIES;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $status = self::STATUS_DRAFT;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $title = self::DEFAULT_TITLE;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $internalNote = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $shipmentTitle = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $paymentTitle = null;

        /**
         * @var Currency
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Currency", inversedBy="supplierOrders")
         * @ORM\JoinColumn(name="currency", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Currency $currency;

        /**
         * @var OrderGroup
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\OrderGroup", inversedBy="supplierOrders")
         * @ORM\JoinColumn(name="order_group", referencedColumnName="id", onDelete="CASCADE")
         */
        protected OrderGroup $orderGroup;

        /**
         * @ORM\ManyToMany(targetEntity="App\Models\Order", inversedBy="supplierOrder")
         * @ORM\JoinTable(
         *      name="supplier_order_to_order_list",
         *      joinColumns={@ORM\JoinColumn(name="supplier_order_id", referencedColumnName="id")},
         *      inverseJoinColumns={@ORM\JoinColumn(name="order_list_id", referencedColumnName="id")}
         * )
         */
        protected ?Collection $orders = null;

        /**
         * @var DateTime|null
         *
         * @ORM\Column(type="date", nullable=true)
         */
        protected ?DateTime $date = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $variableSymbol = null;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $totalPriceWithVat = 0;

        /**
         * @var Customer
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Customer", inversedBy="orders")
         * @ORM\JoinColumn(name="customer", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Customer $customer;

        /**
         * @var Address
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Address", inversedBy="orders")
         * @ORM\JoinColumn(name="delivery_address", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Address $deliveryAddress;

        /**
         * @var Customer
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Customer", inversedBy="subscriberOrders")
         * @ORM\JoinColumn(name="subscriber", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Customer $subscriber;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $centerId = Order::CENTER_ESHOP_ID;

        /**
         * @var DateTime|null
         *
         * @ORM\Column(type="datetime", nullable=true)
         */
        protected ?DateTime $syncDate = null;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\SupplierOrderProduct", mappedBy="supplierOrder")
         */
        protected Collection $supplierOrderProducts;

        public function __construct()
        {
            $this->supplierOrderProducts = new ArrayCollection();
            $this->orders = new ArrayCollection();
        }

        /**
         * @return string
         */
        public function getNumberSeries(): string
        {
            return $this->numberSeries;
        }

        /**
         * @param string $numberSeries
         * @return SupplierOrder
         */
        public function setNumberSeries(string $numberSeries): SupplierOrder
        {
            $this->numberSeries = $numberSeries;
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
         * @return SupplierOrder
         */
        public function setStatus(string $status): SupplierOrder
        {
            $this->status = $status;
            return $this;
        }

        /**
         * @return string
         */
        public function getTitle(): string
        {
            return $this->title;
        }

        /**
         * @param string $title
         * @return SupplierOrder
         */
        public function setTitle(string $title): SupplierOrder
        {
            $this->title = $title;
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
         * @return SupplierOrder
         */
        public function setInternalNote(?string $internalNote): SupplierOrder
        {
            $this->internalNote = $internalNote;
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
         * @return SupplierOrder
         */
        public function setCurrency(Currency $currency): SupplierOrder
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
         * @return SupplierOrder
         */
        public function setOrderGroup(OrderGroup $orderGroup): SupplierOrder
        {
            $this->orderGroup = $orderGroup;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getOrders(): Collection
        {
            return $this->orders;
        }

        /**
         * @param ArrayCollection $orders
         * @return $this
         */
        public function setOrders(ArrayCollection $orders): self
        {
            $this->orders = $orders;

            return $this;
        }

        /**
         * @param Order $order
         * @return void
         */
        public function addOrder(Order $order): void
        {
            $this->orders[] = $order;
        }

        /**
         * @param Order $order
         * @return void
         */
        public function removeOrder(Order $order): void
        {
            $this->orders->removeElement($order);
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
         * @return SupplierOrder
         */
        public function setDate(?DateTime $date): SupplierOrder
        {
            $this->date = $date;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getVariableSymbol(): ?string
        {
            return $this->variableSymbol;
        }

        /**
         * @param string|null $variableSymbol
         * @return SupplierOrder
         */
        public function setVariableSymbol(?string $variableSymbol): SupplierOrder
        {
            $this->variableSymbol = $variableSymbol;
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
         * @return SupplierOrder
         */
        public function setTotalPriceWithVat(float $totalPriceWithVat): SupplierOrder
        {
            $this->totalPriceWithVat = $totalPriceWithVat;
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
         * @return SupplierOrder
         */
        public function setCustomer(Customer $customer): SupplierOrder
        {
            $this->customer = $customer;
            return $this;
        }

        /**
         * @return Address
         */
        public function getDeliveryAddress(): Address
        {
            return $this->deliveryAddress;
        }

        /**
         * @param Address $deliveryAddress
         * @return SupplierOrder
         */
        public function setDeliveryAddress(Address $deliveryAddress): SupplierOrder
        {
            $this->deliveryAddress = $deliveryAddress;
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
         * @return SupplierOrder
         */
        public function setShipmentTitle(?string $shipmentTitle): SupplierOrder
        {
            $this->shipmentTitle = $shipmentTitle;
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
         * @return SupplierOrder
         */
        public function setPaymentTitle(?string $paymentTitle): SupplierOrder
        {
            $this->paymentTitle = $paymentTitle;
            return $this;
        }

        /**
         * @return Customer
         */
        public function getSubscriber(): Customer
        {
            return $this->subscriber;
        }

        /**
         * @param Customer $subscriber
         * @return SupplierOrder
         */
        public function setSubscriber(Customer $subscriber): SupplierOrder
        {
            $this->subscriber = $subscriber;
            return $this;
        }

        /**
         * @return string
         */
        public function getCenterId(): string
        {
            return $this->centerId;
        }

        /**
         * @param string $centerId
         * @return SupplierOrder
         */
        public function setCenterId(string $centerId): SupplierOrder
        {
            $this->centerId = $centerId;
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
         * @return SupplierOrder
         */
        public function setSyncDate(?DateTime $syncDate): SupplierOrder
        {
            $this->syncDate = $syncDate;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getSupplierOrderProducts(): Collection
        {
            return $this->supplierOrderProducts;
        }

        /**
         * @param Collection $supplierOrderProducts
         * @return SupplierOrder
         */
        public function setSupplierOrderProducts(Collection $supplierOrderProducts): SupplierOrder
        {
            $this->supplierOrderProducts = $supplierOrderProducts;
            return $this;
        }
    }