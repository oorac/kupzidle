<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use Doctrine\ORM\Mapping as ORM;
    use Nette\Utils\DateTime;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\DocumentProductRepository")
     * @ORM\Table(name="document_product")
     * @ORM\HasLifecycleCallbacks
     */
    class DocumentProduct implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

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
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $ean = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $productId = null;

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
        protected float $vatRate = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $priceWithVat = 0;

        /**
         * @var Document
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Document", inversedBy="documentProducts")
         * @ORM\JoinColumn(name="document", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Document $document;

        /**
         * @var Product
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Product", inversedBy="documentProducts")
         * @ORM\JoinColumn(name="product", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Product $product;

        /**
         * @return string
         */
        public function getTitle(): string
        {
            return $this->title;
        }

        /**
         * @param string $title
         * @return DocumentProduct
         */
        public function setTitle(string $title): DocumentProduct
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
         * @return DocumentProduct
         */
        public function setCode(string $code): DocumentProduct
        {
            $this->code = $code;
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
         * @return DocumentProduct
         */
        public function setEan(?string $ean): DocumentProduct
        {
            $this->ean = $ean;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getProductId(): ?string
        {
            return $this->productId;
        }

        /**
         * @param string|null $productId
         * @return DocumentProduct
         */
        public function setProductId(?string $productId): DocumentProduct
        {
            $this->productId = $productId;
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
         * @return DocumentProduct
         */
        public function setQuantity(float $quantity): DocumentProduct
        {
            $this->quantity = $quantity;
            return $this;
        }

        /**
         * @return float
         */
        public function getVatRate(): float
        {
            return $this->vatRate;
        }

        /**
         * @param float $vatRate
         * @return DocumentProduct
         */
        public function setVatRate(float $vatRate): DocumentProduct
        {
            $this->vatRate = $vatRate;
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
         * @return DocumentProduct
         */
        public function setPriceWithVat(float $priceWithVat): DocumentProduct
        {
            $this->priceWithVat = $priceWithVat;
            return $this;
        }

        /**
         * @return Document
         */
        public function getDocument(): Document
        {
            return $this->document;
        }

        /**
         * @param Document $document
         * @return DocumentProduct
         */
        public function setDocument(Document $document): DocumentProduct
        {
            $this->document = $document;
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
         * @return DocumentProduct
         */
        public function setProduct(Product $product): DocumentProduct
        {
            $this->product = $product;
            return $this;
        }
    }