<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Attributes\EntityUpdatedOn;
    use App\Models\Interfaces\IEntity;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\ProductParameterRepository")
     * @ORM\Table(name="product_parameter")
     * @ORM\HasLifecycleCallbacks
     */
    class ProductParameter implements IEntity
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
        protected string $name;

        /**
         * @var null|string
         *
         * @ORM\Column(type="string", nullable=true)
         */
        protected ?string $value = null;

        /**
         * @var bool
         *
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $sync = false;

        /**
         * @var Product
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Product", inversedBy="productParameters")
         * @ORM\JoinColumn(name="product", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Product $product;

        /**
         * @return string
         */
        public function getName(): string
        {
            return $this->name;
        }

        /**
         * @param string $name
         * @return ProductParameter
         */
        public function setName(string $name): ProductParameter
        {
            $this->name = $name;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getValue(): ?string
        {
            return $this->value;
        }

        /**
         * @param string|null $value
         * @return ProductParameter
         */
        public function setValue(?string $value): ProductParameter
        {
            $this->value = $value;
            return $this;
        }

        /**
         * @return bool
         */
        public function isSync(): bool
        {
            return $this->sync;
        }

        /**
         * @param bool $sync
         * @return ProductParameter
         */
        public function setSync(bool $sync): ProductParameter
        {
            $this->sync = $sync;
            return $this;
        }

        /**
         * @return Product
         */
        public function getProduct(): Product
        {
            return $this->product;
        }

        /**
         * @param Product $product
         * @return ProductParameter
         */
        public function setProduct(Product $product): ProductParameter
        {
            $this->product = $product;
            return $this;
        }
    }