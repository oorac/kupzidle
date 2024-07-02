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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\CategoryRepository")
     * @ORM\Table(name="category")
     * @ORM\HasLifecycleCallbacks
     */
    class Category implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

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
        protected ?string $code;

        /**
         * @var int
         *
         * @ORM\Column(type="integer", nullable=false)
         */
        protected int $categoryId;

        /**
         * @var bool
         *
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $active = true;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\ProductCategory", mappedBy="category")
         */
        protected Collection $productCategories;

        public function __construct()
        {
            $this->productCategories = new ArrayCollection();
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
         * @return Category
         */
        public function setName(string $name): Category
        {
            $this->name = $name;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getCode(): ?string
        {
            return $this->code;
        }

        /**
         * @param string|null $code
         * @return Category
         */
        public function setCode(?string $code): Category
        {
            $this->code = $code;
            return $this;
        }

        /**
         * @return int
         */
        public function getCategoryId(): int
        {
            return $this->categoryId;
        }

        /**
         * @param int $categoryId
         * @return Category
         */
        public function setCategoryId(int $categoryId): Category
        {
            $this->categoryId = $categoryId;
            return $this;
        }

        /**
         * @return bool
         */
        public function isActive(): bool
        {
            return $this->active;
        }

        /**
         * @param bool $active
         * @return Category
         */
        public function setActive(bool $active): Category
        {
            $this->active = $active;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getProductCategories(): Collection
        {
            return $this->productCategories;
        }

        /**
         * @param Collection $productCategories
         * @return Category
         */
        public function setProductCategories(Collection $productCategories): Category
        {
            $this->productCategories = $productCategories;
            return $this;
        }
    }