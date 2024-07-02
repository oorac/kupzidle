<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use App\Models\Interfaces\IEntityOnStoreCallback;
    use App\Services\Bunny\Bunny;
    use App\Services\DI;
    use App\Tasks\SendMessageTask;
    use DateTime;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="App\Models\Repositories\MessageRepository")
     * @ORM\Table(name="message")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\DiscriminatorColumn(name="discr", type="string")
     * @ORM\HasLifecycleCallbacks
     * @ORM\MappedSuperclass
     */
    abstract class Message implements IEntity, IEntityOnStoreCallback
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        /**
         * @var string
         *
         * @ORM\Column(type="string")
         */
        protected string $recipient = '';

        /**
         * @var resource
         *
         * @ORM\Column(type="blob")
         */
        protected $body;

        /**
         * @var string
         *
         * @ORM\Column(type="string")
         */
        protected string $title = '';

        /**
         * @var DateTime|null
         *
         * @ORM\Column(type="datetime", nullable=true)
         */
        protected ?DateTime $sentOn = null;

        /**
         * @param string $recipient
         * @param string $body
         * @param string $title
         */
        public function __construct(string $recipient, string $body, string $title)
        {
            $this->recipient = $recipient;
            $this->body = bzcompress($body);
            $this->title = $title;
        }

        /**
         * @return string
         */
        public function getTitle(): string
        {
            return $this->title;
        }

        /**
         * @return string
         */
        public function getBody(): string
        {
            return bzdecompress(stream_get_contents($this->body));
        }

        /**
         * @return string
         */
        public function getRecipient(): string
        {
            return $this->recipient;
        }

        /**
         * @return bool
         */
        public function isSent(): bool
        {
            return $this->sentOn !== null;
        }

        /**
         * @return DateTime|null
         */
        public function getSentOn(): ?DateTime
        {
            return $this->sentOn;
        }

        /**
         * @return $this
         */
        public function markSend(): self
        {
            $this->sentOn = new DateTime();

            return $this;
        }

        /**
         * @param DI $di
         * @return callable|null
         */
        public function onStore(DI $di): ?callable
        {
            if ($this->_isNew()) {
                return function () use ($di) {
                    $di->get(Bunny::class)->add(SendMessageTask::create($this->getId()));
                };
            }

            return null;
        }
    }
