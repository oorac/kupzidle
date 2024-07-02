<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\OrderGroupRepository")
     * @ORM\Table(name="order_group")
     * @ORM\HasLifecycleCallbacks
     */
    class OrderGroup implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const CZ_GROUP_ID = '291e2863-b01d-41d2-ba6f-0ac1e43a1759';
        public const SK_GROUP_ID = '7e8933fd-a2c0-490b-b029-23be4b9e2644';
        public const MALL_GROUP_ID = '1bca695e-c087-41cb-9579-4f54384809e9';
        public const CZ_DIC_EUR = '736a6b13-52c3-4b0b-8922-e5e36027087b';
        public const TYPE_SUBSCRIBER = 'SUBSCRIBER';
        public const TYPE_SUPPLIER = 'SUPPLIER';

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
        protected string $type = self::TYPE_SUBSCRIBER;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $vatId;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $currencyCode;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $groupId;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Order", mappedBy="orderGroup")
         */
        protected Collection $orders;

        /**
         * @return string
         */
        public function getTitle(): string
        {
            return $this->title;
        }

        /**
         * @param string $title
         * @return OrderGroup
         */
        public function setTitle(string $title): OrderGroup
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
         * @return OrderGroup
         */
        public function setCode(string $code): OrderGroup
        {
            $this->code = $code;
            return $this;
        }

        /**
         * @return string
         */
        public function getType(): string
        {
            return $this->type;
        }

        /**
         * @param string $type
         * @return OrderGroup
         */
        public function setType(string $type): OrderGroup
        {
            $this->type = $type;
            return $this;
        }

        /**
         * @return string
         */
        public function getVatId(): string
        {
            return $this->vatId;
        }

        /**
         * @param string $vatId
         * @return OrderGroup
         */
        public function setVatId(string $vatId): OrderGroup
        {
            $this->vatId = $vatId;
            return $this;
        }

        /**
         * @return string
         */
        public function getCurrencyCode(): string
        {
            return $this->currencyCode;
        }

        /**
         * @param string $currencyCode
         * @return OrderGroup
         */
        public function setCurrencyCode(string $currencyCode): OrderGroup
        {
            $this->currencyCode = $currencyCode;
            return $this;
        }

        /**
         * @return string
         */
        public function getGroupId(): string
        {
            return $this->groupId;
        }

        /**
         * @param string $groupId
         * @return OrderGroup
         */
        public function setGroupId(string $groupId): OrderGroup
        {
            $this->groupId = $groupId;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getOrders(): Collection
        {
            return $this->orders;
        }

        /**
         * @param Collection $orders
         * @return OrderGroup
         */
        public function setOrders(Collection $orders): OrderGroup
        {
            $this->orders = $orders;
            return $this;
        }

    }