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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\MetaRepository")
     * @ORM\Table(name="meta")
     * @ORM\HasLifecycleCallbacks
     */
    class Meta implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const SK_MOSS = 'sk7';
        public const SK_BRNO = 'sk2';
        public const SK_SUPPLIER = 'sk_dodavatel';

        /**
         * @var string
         *
         * @ORM\Column(type="string", nullable=false)
         */
        protected string $code;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\ProductMeta", mappedBy="meta")
         */
        protected Collection $productMetas;

        public function __construct()
        {
            $this->productMetas = new ArrayCollection();
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
         * @return Meta
         */
        public function setCode(string $code): Meta
        {
            $this->code = $code;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getProductMetas(): Collection
        {
            return $this->productMetas;
        }

        /**
         * @param Collection $productMetas
         * @return Meta
         */
        public function setProductMetas(Collection $productMetas): Meta
        {
            $this->productMetas = $productMetas;
            return $this;
        }

    }