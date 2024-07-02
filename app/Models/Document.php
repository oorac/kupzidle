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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\DocumentRepository")
     * @ORM\Table(name="document")
     * @ORM\HasLifecycleCallbacks
     */
    class Document implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;
        use EntityUpdatedOn;

        public const STATUS_CREATED = 'CREATED';
        public const STATUS_SYNC = 'SYNC';
        public const STATUS_DELETED = 'DELETED';

        /**
         * @var Address
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Address", inversedBy="documents")
         * @ORM\JoinColumn(name="customer", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Address $customer;

        /**
         * @var DateTime
         *
         * @ORM\Column(type="date", nullable=false)
         */
        protected DateTime $date;

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
        protected string $variableSymbol;

        /**
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
        protected bool $paid;

        /**
         * @var Currency
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Currency", inversedBy="documents")
         * @ORM\JoinColumn(name="currency", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Currency $currency;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $totalPriceWithVat = 0;

        /**
         * @var DeliveryMethod
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\DeliveryMethod", inversedBy="documents")
         * @ORM\JoinColumn(name="delivery_method", referencedColumnName="id", onDelete="CASCADE")
         */
        protected DeliveryMethod $deliveryMethod;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $deliveryMethodPriceWithVat = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $deliveryMethodVatRate = 0;

        /**
         * @var PaymentMethod
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\PaymentMethod", inversedBy="documents")
         * @ORM\JoinColumn(name="payment_method", referencedColumnName="id", onDelete="CASCADE")
         */
        protected PaymentMethod $paymentMethod;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $paymentMethodPriceWithVat = 0;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $paymentMethodVatRate = 0;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $customerNote = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $internalNote = null;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\DocumentProduct", mappedBy="document")
         */
        protected Collection $documentProducts;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\DocumentVoucher", mappedBy="document")
         */
        protected Collection $documentVoucher;

        public function __construct()
        {
            $this->documentProducts = new ArrayCollection();
        }

        /**
         * @return Address
         */
        public function getCustomer(): Address
        {
            return $this->customer;
        }

        /**
         * @param Address $customer
         * @return Document
         */
        public function setCustomer(Address $customer): Document
        {
            $this->customer = $customer;
            return $this;
        }

        /**
         * @return DateTime
         */
        public function getDate(): DateTime
        {
            return $this->date;
        }

        /**
         * @param DateTime $date
         * @return Document
         */
        public function setDate(DateTime $date): Document
        {
            $this->date = $date;
            return $this;
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
         * @return Document
         */
        public function setOrderNumber(string $orderNumber): Document
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
         * @return Document
         */
        public function setStatus(string $status): Document
        {
            $this->status = $status;
            return $this;
        }

        /**
         * @return bool
         */
        public function isPaid(): bool
        {
            return $this->paid;
        }

        /**
         * @param bool $paid
         * @return Document
         */
        public function setPaid(bool $paid): Document
        {
            $this->paid = $paid;
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
         * @return Document
         */
        public function setCurrency(Currency $currency): Document
        {
            $this->currency = $currency;
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
         * @return Document
         */
        public function setTotalPriceWithVat(float $totalPriceWithVat): Document
        {
            $this->totalPriceWithVat = $totalPriceWithVat;
            return $this;
        }

        /**
         * @return DeliveryMethod
         */
        public function getDeliveryMethod(): DeliveryMethod
        {
            return $this->deliveryMethod;
        }

        /**
         * @param DeliveryMethod $deliveryMethod
         * @return Document
         */
        public function setDeliveryMethod(DeliveryMethod $deliveryMethod): Document
        {
            $this->deliveryMethod = $deliveryMethod;
            return $this;
        }

        /**
         * @return float
         */
        public function getDeliveryMethodPriceWithVat(): float
        {
            return $this->deliveryMethodPriceWithVat;
        }

        /**
         * @param float $deliveryMethodPriceWithVat
         * @return Document
         */
        public function setDeliveryMethodPriceWithVat(float $deliveryMethodPriceWithVat): Document
        {
            $this->deliveryMethodPriceWithVat = $deliveryMethodPriceWithVat;
            return $this;
        }

        /**
         * @return float
         */
        public function getDeliveryMethodVatRate(): float
        {
            return $this->deliveryMethodVatRate;
        }

        /**
         * @param float $deliveryMethodVatRate
         * @return Document
         */
        public function setDeliveryMethodVatRate(float $deliveryMethodVatRate): Document
        {
            $this->deliveryMethodVatRate = $deliveryMethodVatRate;
            return $this;
        }

        /**
         * @return PaymentMethod
         */
        public function getPaymentMethod(): PaymentMethod
        {
            return $this->paymentMethod;
        }

        /**
         * @param PaymentMethod $paymentMethod
         * @return Document
         */
        public function setPaymentMethod(PaymentMethod $paymentMethod): Document
        {
            $this->paymentMethod = $paymentMethod;
            return $this;
        }

        /**
         * @return float
         */
        public function getPaymentMethodPriceWithVat(): float
        {
            return $this->paymentMethodPriceWithVat;
        }

        /**
         * @param float $paymentMethodPriceWithVat
         * @return Document
         */
        public function setPaymentMethodPriceWithVat(float $paymentMethodPriceWithVat): Document
        {
            $this->paymentMethodPriceWithVat = $paymentMethodPriceWithVat;
            return $this;
        }

        /**
         * @return float
         */
        public function getPaymentMethodVatRate(): float
        {
            return $this->paymentMethodVatRate;
        }

        /**
         * @param float $paymentMethodVatRate
         * @return Document
         */
        public function setPaymentMethodVatRate(float $paymentMethodVatRate): Document
        {
            $this->paymentMethodVatRate = $paymentMethodVatRate;
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
         * @return Document
         */
        public function setVariableSymbol(string $variableSymbol): Document
        {
            $this->variableSymbol = $variableSymbol;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getCustomerNote(): ?string
        {
            return $this->customerNote;
        }

        /**
         * @param string|null $customerNote
         * @return Document
         */
        public function setCustomerNote(?string $customerNote): Document
        {
            $this->customerNote = $customerNote;
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
         * @return Document
         */
        public function setInternalNote(?string $internalNote): Document
        {
            $this->internalNote = $internalNote;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getDocumentProducts(): Collection
        {
            return $this->documentProducts;
        }

        /**
         * @param Collection $documentProducts
         * @return Document
         */
        public function setDocumentProducts(Collection $documentProducts): Document
        {
            $this->documentProducts = $documentProducts;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getDocumentVoucher(): Collection
        {
            return $this->documentVoucher;
        }

        /**
         * @param Collection $documentVoucher
         * @return Document
         */
        public function setDocumentVoucher(Collection $documentVoucher): Document
        {
            $this->documentVoucher = $documentVoucher;
            return $this;
        }


    }