<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use DateTime;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\FeedItemRepository")
     * @ORM\Table(name="feed_item")
     * @ORM\HasLifecycleCallbacks
     */
    class FeedItem implements IEntity
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        /**
         * @var DateTime
         *
         * @ORM\Column(type="datetime", nullable=false)
         */
        protected DateTime $date;

        /**
         * @var bool
         *
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $download;

        /**
         * @var string
         *
         * @ORM\Column(type="text", nullable=false)
         */
        protected string $url;

        /**
         * @var Feed
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Feed", inversedBy="feedItems")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="feed", referencedColumnName="id", onDelete="CASCADE", nullable=false)
         * })
         */
        protected Feed $feed;

        /**
         * @return DateTime
         */
        public function getDate(): DateTime
        {
            return $this->date;
        }

        /**
         * @param DateTime $date
         * @return FeedItem
         */
        public function setDate(DateTime $date): FeedItem
        {
            $this->date = $date;
            return $this;
        }

        /**
         * @return bool
         */
        public function isDownload(): bool
        {
            return $this->download;
        }

        /**
         * @param bool $download
         * @return FeedItem
         */
        public function setDownload(bool $download): FeedItem
        {
            $this->download = $download;
            return $this;
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
         * @return FeedItem
         */
        public function setUrl(string $url): FeedItem
        {
            $this->url = $url;
            return $this;
        }

        /**
         * @return Feed
         */
        public function getFeed(): Feed
        {
            return $this->feed;
        }

        /**
         * @param Feed $feed
         * @return FeedItem
         */
        public function setFeed(Feed $feed): FeedItem
        {
            $this->feed = $feed;
            return $this;
        }

    }