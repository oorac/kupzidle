<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\PredictRepository")
     * @ORM\Table(name="predict")
     * @ORM\HasLifecycleCallbacks
     */
    class Predict implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const TYPE_EMAIL = 'EMAIL';
        public const TYPE_SMS = 'SMS';

        /**
         * @var Package
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Package", inversedBy="predicts")
         * @ORM\JoinColumn(name="package", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Package $package;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $destination = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $destinationType = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $language = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $triggerPredict = null;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $type = self::TYPE_EMAIL;

        /**
         * @return Package
         */
        public function getPackage(): Package
        {
            return $this->package;
        }

        /**
         * @param Package $package
         * @return Predict
         */
        public function setPackage(Package $package): Predict
        {
            $this->package = $package;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getDestination(): ?string
        {
            return $this->destination;
        }

        /**
         * @param string|null $destination
         * @return Predict
         */
        public function setDestination(?string $destination): Predict
        {
            $this->destination = $destination;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getDestinationType(): ?string
        {
            return $this->destinationType;
        }

        /**
         * @param string|null $destinationType
         * @return Predict
         */
        public function setDestinationType(?string $destinationType): Predict
        {
            $this->destinationType = $destinationType;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getLanguage(): ?string
        {
            return $this->language;
        }

        /**
         * @param string|null $language
         * @return Predict
         */
        public function setLanguage(?string $language): Predict
        {
            $this->language = $language;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getTriggerPredict(): ?string
        {
            return $this->triggerPredict;
        }

        /**
         * @param string|null $triggerPredict
         * @return Predict
         */
        public function setTriggerPredict(?string $triggerPredict): Predict
        {
            $this->triggerPredict = $triggerPredict;
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
         * @return Predict
         */
        public function setType(string $type): Predict
        {
            $this->type = $type;
            return $this;
        }

    }