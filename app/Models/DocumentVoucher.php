<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\DocumentVoucherRepository")
     * @ORM\Table(name="document_voucher")
     * @ORM\HasLifecycleCallbacks
     */
    class DocumentVoucher implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $code;

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
         * @ORM\ManyToOne(targetEntity="\App\Models\Document", inversedBy="documentVouchers")
         * @ORM\JoinColumn(name="document", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Document $document;

        /**
         * @var Product
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Product", inversedBy="documentVouchers")
         * @ORM\JoinColumn(name="product", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Product $product;

        /**
         * @var Voucher
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Voucher", inversedBy="documentVouchers")
         * @ORM\JoinColumn(name="voucher", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Voucher $voucher;

        /**
         * @return string
         */
        public function getCode(): string
        {
            return $this->code;
        }

        /**
         * @param string $code
         * @return DocumentVoucher
         */
        public function setCode(string $code): DocumentVoucher
        {
            $this->code = $code;
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
         * @return DocumentVoucher
         */
        public function setQuantity(float $quantity): DocumentVoucher
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
         * @return DocumentVoucher
         */
        public function setVatRate(float $vatRate): DocumentVoucher
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
         * @return DocumentVoucher
         */
        public function setPriceWithVat(float $priceWithVat): DocumentVoucher
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
         * @return DocumentVoucher
         */
        public function setDocument(Document $document): DocumentVoucher
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
         * @return DocumentVoucher
         */
        public function setProduct(Product $product): DocumentVoucher
        {
            $this->product = $product;
            return $this;
        }

        /**
         * @return Voucher
         */
        public function getVoucher(): Voucher
        {
            return $this->voucher;
        }

        /**
         * @param Voucher $voucher
         * @return DocumentVoucher
         */
        public function setVoucher(Voucher $voucher): DocumentVoucher
        {
            $this->voucher = $voucher;
            return $this;
        }
    }