<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Attributes\EntityUpdatedOn;
    use App\Models\Interfaces\IEntity;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\OrderProductRepository")
     * @ORM\Table(name="order_list_product")
     * @ORM\HasLifecycleCallbacks
     */
    class OrderProduct implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;
        use EntityUpdatedOn;

        /**
         * @var Order
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Order", inversedBy="orderProducts")
         * @ORM\JoinColumn(name="order_list", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Order $order;

        /**
         * @var Product
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Product", inversedBy="orderProducts")
         * @ORM\JoinColumn(name="product", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Product $product;

        /**
         * @var null|Store
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Store", inversedBy="orderProducts")
         * @ORM\JoinColumn(name="store", referencedColumnName="id", onDelete="CASCADE", nullable=true)
         */
        protected ?Store $store = null;

        /**
         * @var null|SupplierOrderProduct
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\SupplierOrderProduct", inversedBy="orderProducts")
         * @ORM\JoinColumn(name="supplier_order_product", referencedColumnName="id", onDelete="CASCADE", nullable=true)
         */
        protected ?SupplierOrderProduct $supplierOrderProduct = null;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $quantity = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $vat = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $purchasePrice = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $priceWithoutVat = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $priceWithVat = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $recycleFee = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $pricePerUnit = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $totalPrice = 0;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $availability = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $internalNote = null;

        /**
         * @return Order
         */
        public function getOrder(): Order
        {
            return $this->order;
        }

        /**
         * @param Order $order
         * @return OrderProduct
         */
        public function setOrder(Order $order): OrderProduct
        {
            $this->order = $order;
            return $this;
        }

        /**
         * @return Product
         */
        public function getProduct(): Product
        {
            return $this->product;
        }

        /**
         * @param Product $product
         * @return OrderProduct
         */
        public function setProduct(Product $product): OrderProduct
        {
            $this->product = $product;
            return $this;
        }

        /**
         * @return Store|null
         */
        public function getStore(): ?Store
        {
            return $this->store;
        }

        /**
         * @param Store|null $store
         * @return OrderProduct
         */
        public function setStore(?Store $store): OrderProduct
        {
            $this->store = $store;
            return $this;
        }

        /**
         * @return SupplierOrderProduct|null
         */
        public function getSupplierOrderProduct(): ?SupplierOrderProduct
        {
            return $this->supplierOrderProduct;
        }

        /**
         * @param SupplierOrderProduct|null $supplierOrderProduct
         * @return OrderProduct
         */
        public function setSupplierOrderProduct(?SupplierOrderProduct $supplierOrderProduct): OrderProduct
        {
            $this->supplierOrderProduct = $supplierOrderProduct;
            return $this;
        }

        /**
         * @return float
         */
        public function getQuantity(): float
        {
            return $this->quantity;
        }

        /**
         * @param float $quantity
         * @return OrderProduct
         */
        public function setQuantity(float $quantity): OrderProduct
        {
            $this->quantity = $quantity;
            return $this;
        }

        /**
         * @return float
         */
        public function getVat(): float
        {
            return $this->vat;
        }

        /**
         * @param float $vat
         * @return OrderProduct
         */
        public function setVat(float $vat): OrderProduct
        {
            $this->vat = $vat;
            return $this;
        }

        /**
         * @return float
         */
        public function getPurchasePrice(): float
        {
            return $this->purchasePrice;
        }

        /**
         * @param float $purchasePrice
         * @return OrderProduct
         */
        public function setPurchasePrice(float $purchasePrice): OrderProduct
        {
            $this->purchasePrice = $purchasePrice;
            return $this;
        }

        /**
         * @return float
         */
        public function getPriceWithoutVat(): float
        {
            return $this->priceWithoutVat;
        }

        /**
         * @param float $priceWithoutVat
         * @return OrderProduct
         */
        public function setPriceWithoutVat(float $priceWithoutVat): OrderProduct
        {
            $this->priceWithoutVat = $priceWithoutVat;
            return $this;
        }

        /**
         * @return float
         */
        public function getPriceWithVat(): float
        {
            return $this->priceWithVat;
        }

        /**
         * @param float $priceWithVat
         * @return OrderProduct
         */
        public function setPriceWithVat(float $priceWithVat): OrderProduct
        {
            $this->priceWithVat = $priceWithVat;
            return $this;
        }

        /**
         * @return float
         */
        public function getRecycleFee(): float
        {
            return $this->recycleFee;
        }

        /**
         * @param float $recycleFee
         * @return OrderProduct
         */
        public function setRecycleFee(float $recycleFee): OrderProduct
        {
            $this->recycleFee = $recycleFee;
            return $this;
        }

        /**
         * @return float
         */
        public function getPricePerUnit(): float
        {
            return $this->pricePerUnit;
        }

        /**
         * @param float $pricePerUnit
         * @return OrderProduct
         */
        public function setPricePerUnit(float $pricePerUnit): OrderProduct
        {
            $this->pricePerUnit = $pricePerUnit;
            return $this;
        }

        /**
         * @return float
         */
        public function getTotalPrice(): float
        {
            return $this->totalPrice;
        }

        /**
         * @param float $totalPrice
         * @return OrderProduct
         */
        public function setTotalPrice(float $totalPrice): OrderProduct
        {
            $this->totalPrice = $totalPrice;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getAvailability(): ?string
        {
            return $this->availability;
        }

        /**
         * @param string|null $availability
         * @return OrderProduct
         */
        public function setAvailability(?string $availability): OrderProduct
        {
            $this->availability = $availability;
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
         * @return OrderProduct
         */
        public function setInternalNote(?string $internalNote): OrderProduct
        {
            $this->internalNote = $internalNote;
            return $this;
        }

    }