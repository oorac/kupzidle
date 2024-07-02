<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\ProductCategoryRepository")
     * @ORM\Table(name="product_category")
     * @ORM\HasLifecycleCallbacks
     */
    class ProductCategory implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        /**
         * @var Category
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Category", inversedBy="productCategories")
         * @ORM\JoinColumn(name="category", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Category $category;

        /**
         * @var Product
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Product", inversedBy="productCategories")
         * @ORM\JoinColumn(name="product", referencedColumnName="id", onDelete="CASCADE")
         */
        protected Product $product;

        /**
         * @var int
         *
         * @ORM\Column(type="integer", nullable=false)
         */
        protected int $position = 1;

        /**
         * @var bool
         *
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $main = false;

        /**
         * @return Category
         */
        public function getCategory(): Category
        {
            return $this->category;
        }

        /**
         * @param Category $category
         * @return ProductCategory
         */
        public function setCategory(Category $category): ProductCategory
        {
            $this->category = $category;
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
         * @return ProductCategory
         */
        public function setProduct(Product $product): ProductCategory
        {
            $this->product = $product;
            return $this;
        }

        /**
         * @return int
         */
        public function getPosition(): int
        {
            return $this->position;
        }

        /**
         * @param int $position
         * @return ProductCategory
         */
        public function setPosition(int $position): ProductCategory
        {
            $this->position = $position;
            return $this;
        }

        /**
         * @return bool
         */
        public function isMain(): bool
        {
            return $this->main;
        }

        /**
         * @param bool $main
         * @return ProductCategory
         */
        public function setMain(bool $main): ProductCategory
        {
            $this->main = $main;
            return $this;
        }
    }