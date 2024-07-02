<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use App\Models\Interfaces\IEntityOnStoreCallback;
    use App\Services\DI;
    use App\Utils\Arrays;
    use Doctrine\ORM\Mapping as ORM;
    use Nette\InvalidArgumentException;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\TransactionRepository")
     * @ORM\Table(name="transaction")
     * @ORM\HasLifecycleCallbacks
     */
    class Transaction implements IEntity, IEntityOnStoreCallback
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $transactionId;

        /**
         * @var int
         *
         * @ORM\Column(type="integer", nullable=false)
         */
        protected int $numOrder;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $collectionRequestId = null;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $collectionRequestStatus = null;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $orderNumber = null;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $parcelNumber = null;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $oDepot = null;

        /**
         * @var string|null
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $sDepot = null;

        /**
         * @var array
         *
         * @ORM\Column(type="json", nullable=false)
         */
        protected array $originData = [];

        /**
         * @return string
         */
        public function getTransactionId(): string
        {
            return $this->transactionId;
        }

        /**
         * @param string $transactionId
         * @return Transaction
         */
        public function setTransactionId(string $transactionId): Transaction
        {
            $this->transactionId = $transactionId;
            return $this;
        }

        /**
         * @return int
         */
        public function getNumOrder(): int
        {
            return $this->numOrder;
        }

        /**
         * @param int $numOrder
         * @return Transaction
         */
        public function setNumOrder(int $numOrder): Transaction
        {
            $this->numOrder = $numOrder;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getCollectionRequestId(): ?string
        {
            return $this->collectionRequestId;
        }

        /**
         * @param string|null $collectionRequestId
         * @return Transaction
         */
        public function setCollectionRequestId(?string $collectionRequestId): Transaction
        {
            $this->collectionRequestId = $collectionRequestId;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getCollectionRequestStatus(): ?string
        {
            return $this->collectionRequestStatus;
        }

        /**
         * @param string|null $collectionRequestStatus
         * @return Transaction
         */
        public function setCollectionRequestStatus(?string $collectionRequestStatus): Transaction
        {
            $this->collectionRequestStatus = $collectionRequestStatus;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getOrderNumber(): ?string
        {
            return $this->orderNumber;
        }

        /**
         * @param string|null $orderNumber
         * @return Transaction
         */
        public function setOrderNumber(?string $orderNumber): Transaction
        {
            $this->orderNumber = $orderNumber;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getParcelNumber(): ?string
        {
            return $this->parcelNumber;
        }

        /**
         * @param string|null $parcelNumber
         * @return Transaction
         */
        public function setParcelNumber(?string $parcelNumber): Transaction
        {
            $this->parcelNumber = $parcelNumber;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getODepot(): ?string
        {
            return $this->oDepot;
        }

        /**
         * @param string|null $oDepot
         * @return Transaction
         */
        public function setODepot(?string $oDepot): Transaction
        {
            $this->oDepot = $oDepot;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getSDepot(): ?string
        {
            return $this->sDepot;
        }

        /**
         * @param string|null $sDepot
         * @return Transaction
         */
        public function setSDepot(?string $sDepot): Transaction
        {
            $this->sDepot = $sDepot;
            return $this;
        }

        /**
         * @param array $originData
         * @return Transaction
         */
        public function setOriginData(array $originData): Transaction
        {
            $this->originData = $originData;
            return $this;
        }

        /**
         * @param string|null $property
         * @return array|mixed|null
         */
        private function getOriginData(string $property = null): mixed
        {
            if ($property === null) {
                return $this->originData;
            }

            try {
                return Arrays::get($this->originData, $property);
            } catch (InvalidArgumentException) {
                return null;
            }
        }

        /**
         * @param DI $di
         * @return callable|null
         */
        public function onStore(DI $di): ?callable
        {
            if (! $this->_isNew()) {
                return null;
            }

            return function () use ($di) {
//                $di->get(AdminNotifications::class)->invokeRegisteredNewUser($this);
            };
        }
    }