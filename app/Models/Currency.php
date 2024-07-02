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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\CurrencyRepository")
     * @ORM\Table(name="currency")
     * @ORM\HasLifecycleCallbacks
     */
    class Currency implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const DEFAULT_CURRENCY = 'CZK';

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
         * @ORM\OneToMany(targetEntity="App\Models\Document", mappedBy="currency")
         */
        protected Collection $documents;


        public function __construct()
        {
            $this->documents = new ArrayCollection();
        }

        /**
         * @return string
         */
        public function getTitle(): string
        {
            return $this->title;
        }

        /**
         * @param string $title
         * @return Currency
         */
        public function setTitle(string $title): Currency
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
         * @return Currency
         */
        public function setCode(string $code): Currency
        {
            $this->code = $code;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getDocuments(): Collection
        {
            return $this->documents;
        }

        /**
         * @param Collection $documents
         * @return Currency
         */
        public function setDocuments(Collection $documents): Currency
        {
            $this->documents = $documents;
            return $this;
        }
    }