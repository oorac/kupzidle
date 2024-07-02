<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Attributes\EntityUpdatedOn;
    use App\Models\Interfaces\IEntity;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\ProductMetaRepository")
     * @ORM\Table(name="product_meta")
     * @ORM\HasLifecycleCallbacks
     */
    class ProductMeta implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;
        use EntityUpdatedOn;

        /**
         * @var Meta
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Meta", inversedBy="productMetas")
         * @ORM\JoinColumn(name="meta", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Meta $meta;

        /**
         * @var Product
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Product", inversedBy="productMetas")
         * @ORM\JoinColumn(name="product", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Product $product;

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $value;

        /**
         * @return Meta
         */
        public function getMeta(): Meta
        {
            return $this->meta;
        }

        /**
         * @param Meta $meta
         * @return ProductMeta
         */
        public function setMeta(Meta $meta): ProductMeta
        {
            $this->meta = $meta;
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
         * @return ProductMeta
         */
        public function setProduct(Product $product): ProductMeta
        {
            $this->product = $product;
            return $this;
        }

        /**
         * @return string
         */
        public function getValue(): string
        {
            return $this->value;
        }

        /**
         * @param string $value
         * @return ProductMeta
         */
        public function setValue(string $value): ProductMeta
        {
            $this->value = $value;
            return $this;
        }
    }