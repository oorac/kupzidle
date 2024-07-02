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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\LabelRepository")
     * @ORM\Table(name="label")
     * @ORM\HasLifecycleCallbacks
     */
    class Label implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const COLOR_RECOMMEND = '#fff';
        public const BACKGROUND_RECOMMEND = '0f57ff';
        public const COLOR_DELIVERY_FREE = '#fff';
        public const BACKGROUND_DELIVERY_FREE = '#017d4b';
        public const COLOR_SALE = '#fff';
        public const BACKGROUND_SALE = '#dc3545';

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $name;

        /**
         * @var int
         *
         * @ORM\Column(type="integer", nullable=false)
         */
        protected int $labelId;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $color = '#FFFFFF';

         /**
          * @var string
          *
          * @ORM\Column(type="string", nullable=false)
          */
        protected string $background = '#000000';

        /**
         * @ORM\OneToMany(targetEntity="App\Models\Product", mappedBy="labels")
         */
        protected Collection $products;

        public function __construct()
        {
            $this->products = new ArrayCollection();
        }

        /**
         * @return string
         */
        public function getName(): string
        {
            return $this->name;
        }

        /**
         * @param string $name
         * @return Label
         */
        public function setName(string $name): Label
        {
            $this->name = $name;
            return $this;
        }

        /**
         * @return int
         */
        public function getLabelId(): int
        {
            return $this->labelId;
        }

        /**
         * @param int $labelId
         * @return Label
         */
        public function setLabelId(int $labelId): Label
        {
            $this->labelId = $labelId;
            return $this;
        }

        /**
         * @return string
         */
        public function getColor(): string
        {
            return $this->color;
        }

        /**
         * @param string $color
         * @return Label
         */
        public function setColor(string $color): Label
        {
            $this->color = $color;
            return $this;
        }

        /**
         * @return string
         */
        public function getBackground(): string
        {
            return $this->background;
        }

        /**
         * @param string $background
         * @return Label
         */
        public function setBackground(string $background): Label
        {
            $this->background = $background;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getProducts(): Collection
        {
            return $this->products;
        }

        /**
         * @param Collection $products
         * @return Label
         */
        public function setProducts(Collection $products): Label
        {
            $this->products = $products;
            return $this;
        }
    }