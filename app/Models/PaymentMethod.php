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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\PaymentMethodRepository")
     * @ORM\Table(name="payment_method")
     * @ORM\HasLifecycleCallbacks
     */
    class PaymentMethod implements IEntity
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
         * @var Store
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Store", inversedBy="vouchers")
         * @ORM\JoinColumn(name="store", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Store $store;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Document", mappedBy="paymentMethod")
         */
        protected Collection $documents;

        public function __construct()
        {
            $this->documents = new ArrayCollection();
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
         * @return PaymentMethod
         */
        public function setTitle(string $title): PaymentMethod
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
         * @return PaymentMethod
         */
        public function setCode(string $code): PaymentMethod
        {
            $this->code = $code;
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
         * @return PaymentMethod
         */
        public function setStore(Store $store): PaymentMethod
        {
            $this->store = $store;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getDocuments(): Collection
        {
            return $this->documents;
        }

        /**
         * @param Collection $documents
         * @return PaymentMethod
         */
        public function setDocuments(Collection $documents): PaymentMethod
        {
            $this->documents = $documents;
            return $this;
        }
    }