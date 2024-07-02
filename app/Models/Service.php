<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use DateTime;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\ServiceRepository")
     * @ORM\Table(name="service")
     * @ORM\HasLifecycleCallbacks
     */
    class Service implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const COD_CURRENCY_CZK = 'CZK';
        public const COD_CURRENCY_EUR = 'EUR';

        /**
         * @var Package
         *
         * @ORM\OneToOne(targetEntity="\App\Models\Package", inversedBy="service")
         * @ORM\JoinColumn(name="package", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Package $package;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $parcelWeight = 0;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $mainServiceCode = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $mainServiceElementCodes = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true, length=35)
         */
        protected ?string $ref1 = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true, length=35)
         */
        protected ?string $ref2 = null;

        /**
         * @var DateTime
         *
         * @ORM\Column(type="date", nullable=false)
         */
        protected DateTime $pickupDate;

        /**
         * @var float
         *
         * @ORM\Column(type="float", nullable=false)
         */
        protected float $codAmount = 0;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $codCurrency = self::COD_CURRENCY_CZK;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $codReference = null;

        /**
         * @return Package
         */
        public function getPackage(): Package
        {
            return $this->package;
        }

        /**
         * @param Package $package
         * @return Service
         */
        public function setPackage(Package $package): Service
        {
            $this->package = $package;
            return $this;
        }

        /**
         * @return float
         */
        public function getParcelWeight(): float
        {
            return $this->parcelWeight;
        }

        /**
         * @param float $parcelWeight
         * @return Service
         */
        public function setParcelWeight(float $parcelWeight): Service
        {
            $this->parcelWeight = $parcelWeight;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getMainServiceCode(): ?string
        {
            return $this->mainServiceCode;
        }

        /**
         * @param string|null $mainServiceCode
         * @return Service
         */
        public function setMainServiceCode(?string $mainServiceCode): Service
        {
            $this->mainServiceCode = $mainServiceCode;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getMainServiceElementCodes(): ?string
        {
            return $this->mainServiceElementCodes;
        }

        /**
         * @param string|null $mainServiceElementCodes
         * @return Service
         */
        public function setMainServiceElementCodes(?string $mainServiceElementCodes): Service
        {
            $this->mainServiceElementCodes = $mainServiceElementCodes;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getRef1(): ?string
        {
            return $this->ref1;
        }

        /**
         * @param string|null $ref1
         * @return Service
         */
        public function setRef1(?string $ref1): Service
        {
            $this->ref1 = $ref1;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getRef2(): ?string
        {
            return $this->ref2;
        }

        /**
         * @param string|null $ref2
         * @return Service
         */
        public function setRef2(?string $ref2): Service
        {
            $this->ref2 = $ref2;
            return $this;
        }

        /**
         * @return DateTime
         */
        public function getPickupDate(): DateTime
        {
            return $this->pickupDate;
        }

        /**
         * @param DateTime $pickupDate
         * @return Service
         */
        public function setPickupDate(DateTime $pickupDate): Service
        {
            $this->pickupDate = $pickupDate;
            return $this;
        }

        /**
         * @return float
         */
        public function getCodAmount(): float
        {
            return $this->codAmount;
        }

        /**
         * @param float $codAmount
         * @return Service
         */
        public function setCodAmount(float $codAmount): Service
        {
            $this->codAmount = $codAmount;
            return $this;
        }

        /**
         * @return string
         */
        public function getCodCurrency(): string
        {
            return $this->codCurrency;
        }

        /**
         * @param string $codCurrency
         * @return Service
         */
        public function setCodCurrency(string $codCurrency): Service
        {
            $this->codCurrency = $codCurrency;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getCodReference(): ?string
        {
            return $this->codReference;
        }

        /**
         * @param string|null $codReference
         * @return Service
         */
        public function setCodReference(?string $codReference): Service
        {
            $this->codReference = $codReference;
            return $this;
        }
    }