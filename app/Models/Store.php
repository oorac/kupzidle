<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\StoreRepository")
     * @ORM\Table(name="store")
     * @ORM\HasLifecycleCallbacks
     */
    class Store implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const DEFAULT_SUPPLIER_STORE_CODE = 'SK3';
        public const DEFAULT_BRNO_STORE_CODE = 'SK2';
        public const DEFAULT_MOSS_STORE_CODE = 'SK7';

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $title;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $code;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $storeId;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\ProductStore", mappedBy="store")
         */
        protected Collection $productStores;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Voucher", mappedBy="store")
         */
        protected Collection $vouchers;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\OrderProduct", mappedBy="store")
         */
        protected Collection $orderProducts;


        public function __construct()
        {
            $this->productStores = new ArrayCollection();
            $this->vouchers = new ArrayCollection();
            $this->orderProducts = new ArrayCollection();
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
         * @return Store
         */
        public function setTitle(string $title): Store
        {
            $this->title = $title;
            return $this;
        }

        /**
         * @return string
         */
        public function getCode(): string
        {
            return $this->code;
        }

        /**
         * @param string $code
         * @return Store
         */
        public function setCode(string $code): Store
        {
            $this->code = $code;
            return $this;
        }

        /**
         * @return string
         */
        public function getStoreId(): string
        {
            return $this->storeId;
        }

        /**
         * @param string $storeId
         * @return Store
         */
        public function setStoreId(string $storeId): Store
        {
            $this->storeId = $storeId;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getProductStores(): Collection
        {
            return $this->productStores;
        }

        /**
         * @param Collection $productStores
         * @return Store
         */
        public function setProductStores(Collection $productStores): Store
        {
            $this->productStores = $productStores;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getVouchers(): Collection
        {
            return $this->vouchers;
        }

        /**
         * @param Collection $vouchers
         * @return Store
         */
        public function setVouchers(Collection $vouchers): Store
        {
            $this->vouchers = $vouchers;
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
         * @return Store
         */
        public function setOrderProducts(Collection $orderProducts): Store
        {
            $this->orderProducts = $orderProducts;
            return $this;
        }

    }