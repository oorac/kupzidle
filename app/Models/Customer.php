<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Attributes\EntityUpdatedOn;
    use App\Models\Interfaces\IEntity;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\CustomerRepository")
     * @ORM\Table(name="customer")
     * @ORM\HasLifecycleCallbacks
     */
    class Customer implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;
        use EntityUpdatedOn;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $companyEshopId = null;

        /**
         * @var float
         *
         * @ORM\Column(type="integer", nullable=false)
         */
        protected float $limitOrder = 0;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $companyId = null;

        /**
         * @var Address
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Address", inversedBy="invoiceCustomers")
         * @ORM\JoinColumn(name="address", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Address $invoiceAddress;

        /**
         * @var Address
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Address", inversedBy="deliveryCustomers")
         * @ORM\JoinColumn(name="delivery_address", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Address $deliveryAddress;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Feed", mappedBy="supplier")
         */
        protected Collection $feeds;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Order", mappedBy="customer")
         */
        protected Collection $orders;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Product", mappedBy="supplier")
         */
        protected Collection $products;

        public function __construct()
        {
            $this->feeds = new ArrayCollection();
            $this->orders = new ArrayCollection();
            $this->products = new ArrayCollection();
        }

        /**
         * @return string|null
         */
        public function getCompanyEshopId(): ?string
        {
            return $this->companyEshopId;
        }

        /**
         * @param string|null $companyEshopId
         * @return Customer
         */
        public function setCompanyEshopId(?string $companyEshopId): Customer
        {
            $this->companyEshopId = $companyEshopId;
            return $this;
        }

        /**
         * @return float
         */
        public function getLimitOrder(): float
        {
            return $this->limitOrder;
        }

        /**
         * @param float $limitOrder
         * @return Customer
         */
        public function setLimitOrder(float $limitOrder): Customer
        {
            $this->limitOrder = $limitOrder;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getCompanyId(): ?string
        {
            return $this->companyId;
        }

        /**
         * @param string|null $companyId
         * @return Customer
         */
        public function setCompanyId(?string $companyId): Customer
        {
            $this->companyId = $companyId;
            return $this;
        }

        /**
         * @return Address
         */
        public function getInvoiceAddress(): Address
        {
            return $this->invoiceAddress;
        }

        /**
         * @param Address $invoiceAddress
         * @return Customer
         */
        public function setInvoiceAddress(Address $invoiceAddress): Customer
        {
            $this->invoiceAddress = $invoiceAddress;
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
         * @return Customer
         */
        public function setDeliveryAddress(Address $deliveryAddress): Customer
        {
            $this->deliveryAddress = $deliveryAddress;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getFeeds(): Collection
        {
            return $this->feeds;
        }

        /**
         * @param Collection $feeds
         * @return Customer
         */
        public function setFeeds(Collection $feeds): Customer
        {
            $this->feeds = $feeds;
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
         * @param Collection $orders
         * @return Customer
         */
        public function setOrders(Collection $orders): Customer
        {
            $this->orders = $orders;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getProducts(): Collection
        {
            return $this->products;
        }

        /**
         * @param Collection $products
         * @return Customer
         */
        public function setProducts(Collection $products): Customer
        {
            $this->products = $products;
            return $this;
        }
    }