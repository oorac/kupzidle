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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\PackageRepository")
     * @ORM\Table(name="package")
     * @ORM\HasLifecycleCallbacks
     */
    class Package implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const STATUS_CREATED = 'CREATED';
        public const STATUS_SEND = 'SEND';
        public const STATUS_DELETED = 'DELETED';

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $status = self::STATUS_CREATED;

        /**
         * @var Transaction|null
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Transaction", inversedBy="package")
         * @ORM\JoinColumn(name="transaction", referencedColumnName="id", onDelete="CASCADE")
         */
        protected ?Transaction $transaction = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $numOrder = null;

        /**
         * @var int
         *
         * @ORM\Column(type="integer", nullable=false)
         */
        protected int $countParcel = 1;

        /**
         * @var Address
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Address", inversedBy="packageSender")
         * @ORM\JoinColumn(name="sender", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Address $sender;

        /**
         * @var Address
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Address", inversedBy="packageReceiver")
         * @ORM\JoinColumn(name="receiver", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Address $receiver;

        /**
         * @var Service
         *
         * @ORM\OneToOne(targetEntity="\App\Models\Service", inversedBy="package")
         * @ORM\JoinColumn(name="service", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Service $service;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Predict", mappedBy="package")
         */
        protected Collection $predicts;


        public function __construct()
        {
            $this->predicts = new ArrayCollection();
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
         * @return Package
         */
        public function setStatus(string $status): Package
        {
            $this->status = $status;
            return $this;
        }

        /**
         * @return Transaction|null
         */
        public function getTransaction(): ?Transaction
        {
            return $this->transaction;
        }

        /**
         * @param Transaction|null $transaction
         * @return Package
         */
        public function setTransaction(?Transaction $transaction): Package
        {
            $this->transaction = $transaction;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getNumOrder(): ?string
        {
            return $this->numOrder;
        }

        /**
         * @param string|null $numOrder
         * @return Package
         */
        public function setNumOrder(?string $numOrder): Package
        {
            $this->numOrder = $numOrder;
            return $this;
        }

        /**
         * @return int
         */
        public function getCountParcel(): int
        {
            return $this->countParcel;
        }

        /**
         * @param int $countParcel
         * @return Package
         */
        public function setCountParcel(int $countParcel): Package
        {
            $this->countParcel = $countParcel;
            return $this;
        }

        /**
         * @return Address
         */
        public function getSender(): Address
        {
            return $this->sender;
        }

        /**
         * @param Address $sender
         * @return Package
         */
        public function setSender(Address $sender): Package
        {
            $this->sender = $sender;
            return $this;
        }

        /**
         * @return Company
         */
        public function getReceiver(): Address
        {
            return $this->receiver;
        }

        /**
         * @param Address $receiver
         * @return Package
         */
        public function setReceiver(Address $receiver): Package
        {
            $this->receiver = $receiver;
            return $this;
        }

        /**
         * @return Service
         */
        public function getService(): Service
        {
            return $this->service;
        }

        /**
         * @param Service $service
         * @return Package
         */
        public function setService(Service $service): Package
        {
            $this->service = $service;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getPredicts(): Collection
        {
            return $this->predicts;
        }

        /**
         * @param Collection $predicts
         * @return Package
         */
        public function setPredicts(Collection $predicts): Package
        {
            $this->predicts = $predicts;
            return $this;
        }

    }