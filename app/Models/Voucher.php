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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\VoucherRepository")
     * @ORM\Table(name="voucher")
     * @ORM\HasLifecycleCallbacks
     */
    class Voucher implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;
        use EntityUpdatedOn;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $title;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $code = null;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $ean;

        /**
         * @var Store
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Store", inversedBy="vouchers")
         * @ORM\JoinColumn(name="store", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Store $store;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\DocumentVoucher", mappedBy="voucher")
         */
        protected Collection $vouchers;

        public function __construct()
        {
            $this->vouchers = new ArrayCollection();
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
         * @return Voucher
         */
        public function setTitle(string $title): Voucher
        {
            $this->title = $title;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getCode(): ?string
        {
            return $this->code;
        }

        /**
         * @param string|null $code
         * @return Voucher
         */
        public function setCode(?string $code): Voucher
        {
            $this->code = $code;
            return $this;
        }

        /**
         * @return string
         */
        public function getEan(): string
        {
            return $this->ean;
        }

        /**
         * @param string $ean
         * @return Voucher
         */
        public function setEan(string $ean): Voucher
        {
            $this->ean = $ean;
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
         * @return Voucher
         */
        public function setStore(Store $store): Voucher
        {
            $this->store = $store;
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
         * @return Voucher
         */
        public function setVouchers(Collection $vouchers): Voucher
        {
            $this->vouchers = $vouchers;
            return $this;
        }
    }