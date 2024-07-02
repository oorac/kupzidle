<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use Doctrine\ORM\Mapping as ORM;
    use Nette\Utils\DateTime;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\ProductStoreRepository")
     * @ORM\Table(name="product_store")
     * @ORM\HasLifecycleCallbacks
     */
    class ProductStore implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $quantity = 0;

        /**
         * @var null|DateTime
         *
         * @ORM\Column(type="date", nullable=true)
         */
        protected ?DateTime $stockPlan = null;

        /**
         * @var Store
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Store", inversedBy="productStores")
         * @ORM\JoinColumn(name="store", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Store $store;

        /**
         * @var Product
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Product", inversedBy="productStores")
         * @ORM\JoinColumn(name="product", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Product $product;

        /**
         * @return float
         */
        public function getQuantity(): float
        {
            return $this->quantity;
        }

        /**
         * @param float $quantity
         * @return ProductStore
         */
        public function setQuantity(float $quantity): ProductStore
        {
            $this->quantity = $quantity;
            return $this;
        }

        /**
         * @return DateTime|null
         */
        public function getStockPlan(): ?DateTime
        {
            return $this->stockPlan;
        }

        /**
         * @param DateTime|null $stockPlan
         * @return ProductStore
         */
        public function setStockPlan(?DateTime $stockPlan): ProductStore
        {
            $this->stockPlan = $stockPlan;
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
         * @return ProductStore
         */
        public function setStore(Store $store): ProductStore
        {
            $this->store = $store;
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
         * @return ProductStore
         */
        public function setProduct(Product $product): ProductStore
        {
            $this->product = $product;
            return $this;
        }
    }