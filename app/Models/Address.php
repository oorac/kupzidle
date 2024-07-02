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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\AddressRepository")
     * @ORM\Table(name="address")
     * @ORM\HasLifecycleCallbacks
     */
    class Address implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const CZ_DIC = 'CZ28357418';
        public const SK_DIC = 'SK4120487195';

        public const COUNTRY_CODE_CZ = 'CZ';
        public const COUNTRY_CODE_SK = 'SK';

        public const TYPE_ADDRESS_SUPPLIER = 'SUPPLIER';
        public const TYPE_ADDRESS_SUBSCRIBER = 'SUBSCRIBER';
        public const TYPE_ADDRESS_DEPO = 'DEPO';

        public const ADDRESS_IDEA_NABYTEK_ID = 'bc84e1fb-5829-47a1-8c34-cc03d30c17a4';
        public const ADDRESS_HOUSE_LIFE_ID = '17b161aa-11f7-46b4-bf4f-65b72be8dad1';
        public const ADDRESS_OFFICE_PRO_ID = '5d4d005c-646d-4a83-bb6c-575e1cc83bef';
        public const ADDRESS_AUTRONIC_ID = 'ad15b39b-2f90-427a-b0aa-3faf62293ece';
        public const ADDRESS_TEMPO_KONDELA_ID = '6be3bc7d-7f28-4ea0-93c2-cbab0d113ca8';
        public const ADDRESS_ALBA_ID ='66324ed6-ff20-4335-a680-8b82a7b06def';
        public const ADDRESS_HALMAR_ID ='1fa3139e-9e75-40d5-a153-630866d7ad91';
        public const ADDRESS_BIBL_ID ='1dabd40a-f24a-4378-bc3e-73f884a02a4a';
        public const ADDRESS_FASTJUMP_ID ='a66ee46d-f6c6-4b8f-9b71-081f4ebf64c7';
        public const ADDRESS_LD_SEATING_ID = '59f9b729-d820-43c3-b844-df8e6a415269';
        public const ADDRESS_KAVING_SIT_ID ='b1485e03-383b-459e-91db-e0a263211ab4';
        public const ADDRESS_ROJAPLAST_ID = '1b2676e9-957d-4bf2-8560-49e984216152';
        public const ADDRESS_ITTC_STIMA_ID = 'f5e2c296-bd2e-4222-bf7d-5f125c9e8ee6';
        public const ADDRESS_PROWORK_ID = '3f077635-3551-4c0e-ae57-b603b63ae7f6';
        public const ADDRESS_MAYER_ID = '4c038b7e-0412-42dd-9639-b4357fdddfcb';
        public const ADDRESS_FIBER_MOUNTS_ID = 'ef808c73-3889-4448-9c0b-62bdafcba92a';
        public const ADDRESS_ANTARES_ID = '2c87242d-21fe-44be-b25b-5594eaa14639';
        public const ADDRESS_BRADOP_ID = '4935341f-b89b-4551-9adf-45ad2cc69b07';
        public const ADDRESS_FLOKK_ID = '30023668-83fa-492f-be3f-505cb51a68e6';
        public const ADDRESS_SIGNAL_ID ='a74d6148-2c3e-47bd-8a02-0d83025dd456';
        public const ADDRESS_SEGO_ID = 'de3e7aff-e197-44e3-9897-8a5487d9f245';
        public const ADDRESS_RIM_CZ_ID = '75a0208e-1cfe-41ed-9eb5-8b47aef6c3c1';
        public const ADDRESS_LAMA_PLUS_ID = '43f5d18c-cc97-4826-abf8-ed693b71b8a8';
        public const ADDRESS_OFFICE_MORE_ID = 'dd939190-50af-4ea2-90a1-9d6ed257bb08';
        public const ADDRESS_HON_ID = 'b435b9dc-fd90-435d-9f6f-90bd57e22de6';
        public const ADDRESS_ADK_TRADE_ID = '234de704-04d9-488a-a9b3-4f6fc2c6629c';
        public const ADDRESS_DESIGNOVE_ZIDLE_ID = '7d0b6f3d-d3eb-45e9-bd6f-2a3e4d29101c';
        public const ADDRESS_AXIN_TRADING_ID = 'fd8dca36-bfc3-4894-9f1e-8c7389a67041';

        /**
         * @var string
         *
         * @ORM\Column(type="string")
         */
        protected string $type = self::TYPE_ADDRESS_SUPPLIER;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $title = null;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $companyName;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $contactName = null;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $countryCode = self::COUNTRY_CODE_CZ;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $street = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $city = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $zipCode = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $phone = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $email = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $crnId = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $vatId = null;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Customer", mappedBy="invoiceAddress")
         */
        protected Collection $invoiceCustomers;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Customer", mappedBy="deliveryAddress")
         */
        protected Collection $deliveryCustomers;


        public function __construct()
        {
            $this->invoiceCustomers = new ArrayCollection();
            $this->deliveryCustomers = new ArrayCollection();
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
         * @return Address
         */
        public function setType(string $type): Address
        {
            $this->type = $type;
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
         * @return Address
         */
        public function setTitle(?string $title): Address
        {
            $this->title = $title;
            return $this;
        }

        /**
         * @return string
         */
        public function getCompanyName(): string
        {
            return $this->companyName;
        }

        /**
         * @param string $companyName
         * @return Address
         */
        public function setCompanyName(string $companyName): Address
        {
            $this->companyName = $companyName;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getContactName(): ?string
        {
            return $this->contactName;
        }

        /**
         * @param string|null $contactName
         * @return Address
         */
        public function setContactName(?string $contactName): Address
        {
            $this->contactName = $contactName;
            return $this;
        }

        /**
         * @return string
         */
        public function getCountryCode(): string
        {
            return $this->countryCode;
        }

        /**
         * @param string $countryCode
         * @return Address
         */
        public function setCountryCode(string $countryCode): Address
        {
            $this->countryCode = $countryCode;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getStreet(): ?string
        {
            return $this->street;
        }

        /**
         * @param string|null $street
         * @return Address
         */
        public function setStreet(?string $street): Address
        {
            $this->street = $street;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getCity(): ?string
        {
            return $this->city;
        }

        /**
         * @param string|null $city
         * @return Address
         */
        public function setCity(?string $city): Address
        {
            $this->city = $city;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getZipCode(): ?string
        {
            return $this->zipCode;
        }

        /**
         * @param string|null $zipCode
         * @return Address
         */
        public function setZipCode(?string $zipCode): Address
        {
            $this->zipCode = $zipCode;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getPhone(): ?string
        {
            return $this->phone;
        }

        /**
         * @param string|null $phone
         * @return Address
         */
        public function setPhone(?string $phone): Address
        {
            $this->phone = $phone;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getEmail(): ?string
        {
            return $this->email;
        }

        /**
         * @param string|null $email
         * @return Address
         */
        public function setEmail(?string $email): Address
        {
            $this->email = $email;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getCrnId(): ?string
        {
            return $this->crnId;
        }

        /**
         * @param string|null $crnId
         * @return Address
         */
        public function setCrnId(?string $crnId): Address
        {
            $this->crnId = $crnId;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getVatId(): ?string
        {
            return $this->vatId;
        }

        /**
         * @param string|null $vatId
         * @return Address
         */
        public function setVatId(?string $vatId): Address
        {
            $this->vatId = $vatId;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getInvoiceCustomers(): Collection
        {
            return $this->invoiceCustomers;
        }

        /**
         * @return Collection
         */
        public function getDeliveryCustomers(): Collection
        {
            return $this->deliveryCustomers;
        }

    }