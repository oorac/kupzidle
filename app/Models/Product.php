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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\ProductRepository")
     * @ORM\Table(name="product",
     *      indexes={
     *          @ORM\Index(name="product_code_idx", columns={"productCode"}),
     *          @ORM\Index(name="article_id_idx", columns={"articleId"})
     *      },
     *     uniqueConstraints={
     *          @ORM\UniqueConstraint(name="unique_product_code", columns={"productCode"}),
     *          @ORM\UniqueConstraint(name="unique_article_id", columns={"articleId"})
     *      }
     * )
     * @ORM\HasLifecycleCallbacks
     */
    class Product implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;
        use EntityUpdatedOn;

        public const DEFAULT_VOUCHER_TITLE = "Slevový kupón";
        public const DEFAULT_AVAILABILITY_ESHOP = "Není skladem";
        public const DEFAULT_VOUCHER_CODE = "SLEVA";
        public const STATUS_CREATED = 'CREATED';
        public const STATUS_SYNC = 'SYNC';
        public const STATUS_DELETED = 'DELETED';

        public const STOCK_AVAILABLE = "AVAILABLE";
        public const STOCK_UNAVAILABLE = "UNAVAILABLE";
        public const STOCK_EXPEDITION_5 = "EXPEDITION_TO_5";
        public const STOCK_EXPEDITION_7 = "EXPEDITION_TO_7";
        public const STOCK_EXPEDITION_10 = "EXPEDITION_TO_10";
        public const STOCK_DELIVERY_3 = "DELIVERY_3";
        public const STOCK_DELIVERY_5 = "DELIVERY_5";
        public const STOCK_DELIVERY_7 = "DELIVERY_7";
        public const STOCK_DELIVERY_10 = "DELIVERY_10";

        public const AVAILABILITY_TEXT_ARRAY = [
            'Skladem' => 1,
            'Skladem poslední 3 kusy' => 1,
            'Skladem v Brně' => 2,
            'Dodání za 3-5 pracovních dní' => 4,
            'Dodání 5-7 pracovních dní' => 6,
            'Expedujeme za 5-7 pracovních dní' => 7,
            'Expedujeme za 7-10 pracovních dní' => 9,
            'Expedujeme za 12-14 pracovních dní' => 11,
            'Doba dodání 3-4 týdny' => 12,
            'Plánované naskladnění je' => 13,
            'Dostupnost na dotaz' => 14,
            'Na dotaz' => 14,
            'Není skladem' => 15,
        ];

        /**
         * Informace, zda je synchronizován s Money. SYNC ano, CREATED ne, DELETED smazán
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $status = self::STATUS_CREATED;

        /**
         * @var bool
         *
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $voucher = false;

        /**
         * @var bool
         *
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $archive = false;

        /**
         * @var bool
         *
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $moneySync = false;

        /**
         * @var null|Customer
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Customer", inversedBy="products")
         * @ORM\JoinColumn(name="supplier", referencedColumnName="id", onDelete="CASCADE", nullable=true)
         */
        protected ?Customer $supplier = null;

        /**
         * @var null|Product
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Product", inversedBy="productVariants")
         * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="CASCADE", nullable=true)
         */
        protected ?Product $parent = null;

        /**
         * PRODUCT_ID z e-shopu
         * @var null|int
         *
         * @ORM\Column(type="integer", nullable=true)
         */
        protected ?int $productId = null;

        /**
         * Informace o počtu kusů na e-shopu
         * @var null|int
         *
         * @ORM\Column(type="integer", nullable=true)
         */
        protected ?int $stockEshop = null;

        /**
         * Textová informace o stavu na e-shopu
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $availabilityEshop = null;

        /**
         * Dodavatel / Výrobce
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $manufacturer = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $ean = null;

        /**
         * Kód pro prování s e-shopem
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $productCode = null;

        /**
         * ID položky v Money
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $articleId = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $title = null;

        /**
         * Odkaz na e-shopu
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $url = null;

        /**
         * Odkaz na hlavní obrázek
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $imageLink = null;

        /**
         * Dodavatelský kod z Money
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $supplierCode = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $plu = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="text", nullable=true)
         */
        protected ?string $note = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="text", nullable=true)
         */
        protected ?string $description = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="text", nullable=true)
         */
        protected ?string $longDescription = null;

        /**
         * @var int
         *
         * @ORM\Column(type="integer", nullable=false)
         */
        protected int $deliveryCount = 1;

        /**
         * @var int
         *
         * @ORM\Column(type="integer", nullable=false)
         */
        protected int $package = 1;

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
        protected float $width = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $height = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $length = 0;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $stock = self::STOCK_AVAILABLE;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $totalQuantity = 0;

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
        protected float $standardPrice = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $actualPrice = 0;

        /**
         * @ORM\ManyToMany(targetEntity="App\Models\Label", inversedBy="products")
         * @ORM\JoinTable(
         *      name="product_label",
         *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
         *      inverseJoinColumns={@ORM\JoinColumn(name="label_id", referencedColumnName="id")}
         * )
         */
        protected ?Collection $labels = null;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\ProductStore", mappedBy="product")
         */
        protected Collection $productStores;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\ProductParameter", mappedBy="product")
         */
        protected Collection $productParameters;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\ProductCategory", mappedBy="product")
         */
        protected Collection $productCategories;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\OrderProduct", mappedBy="product")
         */
        protected Collection $orderProducts;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\ProductMeta", mappedBy="product")
         */
        protected Collection $productMetas;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Product", mappedBy="parent")
         */
        protected Collection $productVariants;

        public function __construct()
        {
            $this->productStores = new ArrayCollection();
            $this->productParameters = new ArrayCollection();
            $this->productCategories = new ArrayCollection();
            $this->orderProducts = new ArrayCollection();
            $this->productMetas = new ArrayCollection();
            $this->productVariants = new ArrayCollection();
        }

        /**
         * @return bool
         */
        public function isVoucher(): bool
        {
            return $this->voucher;
        }

        /**
         * @param bool $voucher
         * @return Product
         */
        public function setVoucher(bool $voucher): Product
        {
            $this->voucher = $voucher;
            return $this;
        }

        /**
         * @return bool
         */
        public function isArchive(): bool
        {
            return $this->archive;
        }

        /**
         * @param bool $archive
         * @return Product
         */
        public function setArchive(bool $archive): Product
        {
            $this->archive = $archive;
            return $this;
        }

        /**
         * @return bool
         */
        public function isMoneySync(): bool
        {
            return $this->moneySync;
        }

        /**
         * @param bool $moneySync
         * @return Product
         */
        public function setMoneySync(bool $moneySync): Product
        {
            $this->moneySync = $moneySync;
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
         * @return Product
         */
        public function setStatus(string $status): Product
        {
            $this->status = $status;
            return $this;
        }

        /**
         * @return Product|null
         */
        public function getParent(): ?Product
        {
            return $this->parent;
        }

        /**
         * @param Product|null $parent
         * @return Product
         */
        public function setParent(?Product $parent): Product
        {
            $this->parent = $parent;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getProductVariants(): Collection
        {
            return $this->productVariants;
        }

        /**
         * @param Collection $productVariants
         * @return Product
         */
        public function setProductVariants(Collection $productVariants): Product
        {
            $this->productVariants = $productVariants;
            return $this;
        }

        /**
         * @return Customer|null
         */
        public function getSupplier(): ?Customer
        {
            return $this->supplier;
        }

        /**
         * @param Customer|null $supplier
         * @return Product
         */
        public function setSupplier(?Customer $supplier): Product
        {
            $this->supplier = $supplier;
            return $this;
        }

        /**
         * @return int|null
         */
        public function getProductId(): ?int
        {
            return $this->productId;
        }

        /**
         * @param int|null $productId
         * @return Product
         */
        public function setProductId(?int $productId): Product
        {
            $this->productId = $productId;
            return $this;
        }

        /**
         * @return int|null
         */
        public function getStockEshop(): ?int
        {
            return $this->stockEshop;
        }

        /**
         * @param int|null $stockEshop
         * @return Product
         */
        public function setStockEshop(?int $stockEshop): Product
        {
            $this->stockEshop = $stockEshop;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getAvailabilityEshop(): ?string
        {
            return $this->availabilityEshop;
        }

        /**
         * @param string|null $availabilityEshop
         * @return Product
         */
        public function setAvailabilityEshop(?string $availabilityEshop): Product
        {
            $this->availabilityEshop = $availabilityEshop;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getManufacturer(): ?string
        {
            return $this->manufacturer;
        }

        /**
         * @param string|null $manufacturer
         * @return Product
         */
        public function setManufacturer(?string $manufacturer): Product
        {
            $this->manufacturer = $manufacturer;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getEan(): ?string
        {
            return $this->ean;
        }

        /**
         * @param string|null $ean
         * @return Product
         */
        public function setEan(?string $ean): Product
        {
            $this->ean = $ean;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getProductCode(): ?string
        {
            return $this->productCode;
        }

        /**
         * @param string|null $productCode
         * @return Product
         */
        public function setProductCode(?string $productCode): Product
        {
            $this->productCode = $productCode;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getArticleId(): ?string
        {
            return $this->articleId;
        }

        /**
         * @param string|null $articleId
         * @return Product
         */
        public function setArticleId(?string $articleId): Product
        {
            $this->articleId = $articleId;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getTitle(): ?string
        {
            return $this->title;
        }

        /**
         * @param string|null $title
         * @return Product
         */
        public function setTitle(?string $title): Product
        {
            $this->title = $title;
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
         * @return Product
         */
        public function setUrl(?string $url): Product
        {
            $this->url = $url;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getImageLink(): ?string
        {
            return $this->imageLink;
        }

        /**
         * @param string|null $imageLink
         * @return Product
         */
        public function setImageLink(?string $imageLink): Product
        {
            $this->imageLink = $imageLink;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getSupplierCode(): ?string
        {
            return $this->supplierCode;
        }

        /**
         * @param string|null $supplierCode
         * @return Product
         */
        public function setSupplierCode(?string $supplierCode): Product
        {
            $this->supplierCode = $supplierCode;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getPlu(): ?string
        {
            return $this->plu;
        }

        /**
         * @param string|null $plu
         * @return Product
         */
        public function setPlu(?string $plu): Product
        {
            $this->plu = $plu;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getNote(): ?string
        {
            return $this->note;
        }

        /**
         * @param string|null $note
         * @return Product
         */
        public function setNote(?string $note): Product
        {
            $this->note = $note;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getDescription(): ?string
        {
            return $this->description;
        }

        /**
         * @param string|null $description
         * @return Product
         */
        public function setDescription(?string $description): Product
        {
            $this->description = $description;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getLongDescription(): ?string
        {
            return $this->longDescription;
        }

        /**
         * @param string|null $longDescription
         * @return Product
         */
        public function setLongDescription(?string $longDescription): Product
        {
            $this->longDescription = $longDescription;
            return $this;
        }

        /**
         * @return int
         */
        public function getDeliveryCount(): int
        {
            return $this->deliveryCount;
        }

        /**
         * @param int $deliveryCount
         * @return Product
         */
        public function setDeliveryCount(int $deliveryCount): Product
        {
            $this->deliveryCount = $deliveryCount;
            return $this;
        }

        /**
         * @return int
         */
        public function getPackage(): int
        {
            return $this->package;
        }

        /**
         * @param int $package
         * @return Product
         */
        public function setPackage(int $package): Product
        {
            $this->package = $package;
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
         * @return Product
         */
        public function setWeight(float $weight): Product
        {
            $this->weight = $weight;
            return $this;
        }

        /**
         * @return float
         */
        public function getWidth(): float
        {
            return $this->width;
        }

        /**
         * @param float $width
         * @return Product
         */
        public function setWidth(float $width): Product
        {
            $this->width = $width;
            return $this;
        }

        /**
         * @return float
         */
        public function getHeight(): float
        {
            return $this->height;
        }

        /**
         * @param float $height
         * @return Product
         */
        public function setHeight(float $height): Product
        {
            $this->height = $height;
            return $this;
        }

        /**
         * @return float
         */
        public function getLength(): float
        {
            return $this->length;
        }

        /**
         * @param float $length
         * @return Product
         */
        public function setLength(float $length): Product
        {
            $this->length = $length;
            return $this;
        }

        /**
         * @return string
         */
        public function getStock(): string
        {
            return $this->stock;
        }

        /**
         * @param string $stock
         * @return Product
         */
        public function setStock(string $stock): Product
        {
            $this->stock = $stock;
            return $this;
        }

        /**
         * @return float
         */
        public function getTotalQuantity(): float
        {
            return $this->totalQuantity;
        }

        /**
         * @param float $totalQuantity
         * @return Product
         */
        public function setTotalQuantity(float $totalQuantity): Product
        {
            $this->totalQuantity = $totalQuantity;
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
         * @return Product
         */
        public function setPurchasePrice(float $purchasePrice): Product
        {
            $this->purchasePrice = $purchasePrice;
            return $this;
        }

        /**
         * @return float
         */
        public function getStandardPrice(): float
        {
            return $this->standardPrice;
        }

        /**
         * @param float $standardPrice
         * @return Product
         */
        public function setStandardPrice(float $standardPrice): Product
        {
            $this->standardPrice = $standardPrice;
            return $this;
        }

        /**
         * @return float
         */
        public function getActualPrice(): float
        {
            return $this->actualPrice;
        }

        /**
         * @param float $actualPrice
         * @return Product
         */
        public function setActualPrice(float $actualPrice): Product
        {
            $this->actualPrice = $actualPrice;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getLabels(): Collection
        {
            return $this->labels;
        }

        /**
         * @param ArrayCollection $labels
         * @return $this
         */
        public function setLabels(ArrayCollection $labels): self
        {
            $this->labels = $labels;

            return $this;
        }

        /**
         * @param Label $label
         * @return void
         */
        public function addLabel(Label $label): void
        {
            $this->labels[] = $label;
        }

        /**
         * @param Label $label
         * @return void
         */
        public function removeLabel(Label $label): void
        {
            $this->labels->removeElement($label);
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
         * @return Product
         */
        public function setProductStores(Collection $productStores): Product
        {
            $this->productStores = $productStores;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getProductParameters(): Collection
        {
            return $this->productParameters;
        }

        /**
         * @param Collection $productParameters
         * @return Product
         */
        public function setProductParameters(Collection $productParameters): Product
        {
            $this->productParameters = $productParameters;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getProductCategories(): Collection
        {
            return $this->productCategories;
        }

        /**
         * @param Collection $productCategories
         * @return Product
         */
        public function setProductCategories(Collection $productCategories): Product
        {
            $this->productCategories = $productCategories;
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
         * @return Product
         */
        public function setOrderProducts(Collection $orderProducts): Product
        {
            $this->orderProducts = $orderProducts;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getProductMetas(): Collection
        {
            return $this->productMetas;
        }

        /**
         * @param Collection $productMetas
         * @return Product
         */
        public function setProductMetas(Collection $productMetas): Product
        {
            $this->productMetas = $productMetas;
            return $this;
        }

    }