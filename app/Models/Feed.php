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
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\FeedRepository")
     * @ORM\Table(name="feed")
     * @ORM\HasLifecycleCallbacks
     */
    class Feed implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const DIR_FEED_XML = DIR_WWW . DS . 'import/xml/';
        public const DIR_FEED_XSLT = DIR_WWW . DS . 'import/xslt/';
        public const DIR_FEED_OUTPUT = DIR_WWW . DS . 'import/output/';
        public const DIR_FEED_SAXON = DIR_WWW . DS . 'import/SaxonEE10-9J/saxon-ee-10.9.jar';

        public const TYPE_IMPORT = 'IMPORT';
        public const TYPE_EXPORT = 'EXPORT';
        public const SUBTYPE_PRODUCT = 'PRODUCT';
        public const SUBTYPE_ORDER = 'ORDER';

        /**
         * @var string
         *
         * @ORM\Column(type="text", nullable=false)
         */
        protected string $url;

        /**
         * @var string
         *
         * @ORM\Column(type="text", nullable=false)
         */
        protected string $xslFileName;

        /**
         * @var string
         *
         * @ORM\Column(type="text", nullable=false)
         */
        protected string $outputName;

        /**
         * @var string
         *
         * @ORM\Column(type="text", nullable=false)
         */
        protected string $type = self::TYPE_IMPORT;

        /**
         * @var string
         *
         * @ORM\Column(type="text", nullable=false)
         */
        protected string $subType = self::SUBTYPE_PRODUCT;

        /**
         * @var null|string
         *
         * @ORM\Column(type="text", nullable=true)
         */
        protected ?string $username = null;

        /**
         * @var null|string
         *
         * @ORM\Column(type="text", nullable=true)
         */
        protected ?string $password = null;

        /**
         * @var null|Customer
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Customer", inversedBy="feeds")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="supplier", referencedColumnName="id", onDelete="CASCADE", nullable=true)
         * })
         */
        protected ?Customer $supplier;

        /**
         * @ORM\OneToMany(targetEntity="App\Models\FeedItem", mappedBy="feed")
         */
        protected Collection $feedItems;


        public function __construct()
        {
            $this->feedItems = new ArrayCollection();
        }

        /**
         * @return string
         */
        public function getUrl(): string
        {
            return $this->url;
        }

        /**
         * @param string $url
         * @return Feed
         */
        public function setUrl(string $url): Feed
        {
            $this->url = $url;
            return $this;
        }

        /**
         * @return string
         */
        public function getXslFileName(): string
        {
            return $this->xslFileName;
        }

        /**
         * @param string $xslFileName
         * @return Feed
         */
        public function setXslFileName(string $xslFileName): Feed
        {
            $this->xslFileName = $xslFileName;
            return $this;
        }

        /**
         * @return string
         */
        public function getOutputName(): string
        {
            return $this->outputName;
        }

        /**
         * @param string $outputName
         * @return Feed
         */
        public function setOutputName(string $outputName): Feed
        {
            $this->outputName = $outputName;
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
         * @return Feed
         */
        public function setType(string $type): Feed
        {
            $this->type = $type;
            return $this;
        }

        /**
         * @return string
         */
        public function getSubType(): string
        {
            return $this->subType;
        }

        /**
         * @param string $subType
         * @return Feed
         */
        public function setSubType(string $subType): Feed
        {
            $this->subType = $subType;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getUsername(): ?string
        {
            return $this->username;
        }

        /**
         * @param string|null $username
         * @return Feed
         */
        public function setUsername(?string $username): Feed
        {
            $this->username = $username;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getPassword(): ?string
        {
            return $this->password;
        }

        /**
         * @param string|null $password
         * @return Feed
         */
        public function setPassword(?string $password): Feed
        {
            $this->password = $password;
            return $this;
        }

        /**
         * @return Customer|null
         */
        public function getSupplier(): ?Customer
        {
            return $this->supplier;
        }

        /**
         * @param Customer|null $supplier
         * @return Feed
         */
        public function setSupplier(?Customer $supplier): Feed
        {
            $this->supplier = $supplier;
            return $this;
        }

        /**
         * @return Collection
         */
        public function getFeedItems(): Collection
        {
            return $this->feedItems;
        }

        /**
         * @param Collection $feedItems
         * @return Feed
         */
        public function setFeedItems(Collection $feedItems): Feed
        {
            $this->feedItems = $feedItems;
            return $this;
        }

    }