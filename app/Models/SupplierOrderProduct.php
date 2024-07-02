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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\SupplierOrderProductRepository")
     * @ORM\Table(name="supplier_order_list_product")
     * @ORM\HasLifecycleCallbacks
     */
    class SupplierOrderProduct implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;
        use EntityUpdatedOn;

        /**
         * @var SupplierOrder
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\SupplierOrder", inversedBy="supplierOrderProducts")
         * @ORM\JoinColumn(name="order_list", referencedColumnName="id", onDelete="CASCADE")
         */
        protected SupplierOrder $supplierOrder;

        /**
         * @var Product
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Product", inversedBy="supplierOrderProducts")
         * @ORM\JoinColumn(name="product", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Product $product;

        /**
         * @var Store
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Store", inversedBy="supplierOrderProducts")
         * @ORM\JoinColumn(name="store", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Store $store;

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
        protected ?string $internalNote = null;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\OrderProduct", mappedBy="supplierOrderProduct")
         */
        protected Collection $orderProducts;

        public function __construct()
        {
            $this->orderProducts = new ArrayCollection();
        }

        /**
         * @return SupplierOrder
         */
        public function getSupplierOrder(): SupplierOrder
        {
            return $this->supplierOrder;
        }

        /**
         * @param SupplierOrder $supplierOrder
         * @return SupplierOrderProduct
         */
        public function setSupplierOrder(SupplierOrder $supplierOrder): SupplierOrderProduct
        {
            $this->supplierOrder = $supplierOrder;
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
         * @return SupplierOrderProduct
         */
        public function setProduct(Product $product): SupplierOrderProduct
        {
            $this->product = $product;
            return $this;
        }

        /**
         * @return Store
         */
        public function getStore(): Store
        {
            return $this->store;
        }

        /**
         * @param Store $store
         * @return SupplierOrderProduct
         */
        public function setStore(Store $store): SupplierOrderProduct
        {
            $this->store = $store;
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
         * @return SupplierOrderProduct
         */
        public function setQuantity(float $quantity): SupplierOrderProduct
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
         * @return SupplierOrderProduct
         */
        public function setVat(float $vat): SupplierOrderProduct
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
         * @return SupplierOrderProduct
         */
        public function setPurchasePrice(float $purchasePrice): SupplierOrderProduct
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
         * @return SupplierOrderProduct
         */
        public function setPriceWithoutVat(float $priceWithoutVat): SupplierOrderProduct
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
         * @return SupplierOrderProduct
         */
        public function setPriceWithVat(float $priceWithVat): SupplierOrderProduct
        {
            $this->priceWithVat = $priceWithVat;
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
         * @return SupplierOrderProduct
         */
        public function setPricePerUnit(float $pricePerUnit): SupplierOrderProduct
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
         * @return SupplierOrderProduct
         */
        public function setTotalPrice(float $totalPrice): SupplierOrderProduct
        {
            $this->totalPrice = $totalPrice;
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
         * @return SupplierOrderProduct
         */
        public function setInternalNote(?string $internalNote): SupplierOrderProduct
        {
            $this->internalNote = $internalNote;
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
         * @return SupplierOrderProduct
         */
        public function setOrderProducts(Collection $orderProducts): SupplierOrderProduct
        {
            $this->orderProducts = $orderProducts;
            return $this;
        }

    }