<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use App\Models\Interfaces\IEntityOnStoreCallback;
    use App\Services\AdminNotifications;
    use App\Services\Deadpool\Deadpool;
    use App\Services\DI;
    use DateTime;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\UserRepository")
     * @ORM\Table(name="user")
     * @ORM\HasLifecycleCallbacks
     */
    class User implements IEntity, IEntityOnStoreCallback
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const ROLE_GUEST = 'GUEST';
        public const ROLE_USER = 'USER';
        public const ROLE_ADMIN = 'ADMIN';

        public const TYPE_ROLES = [
            self::ROLE_GUEST => 'Host',
            self::ROLE_USER => 'Standardní uživatel',
            self::ROLE_ADMIN => 'Admin',
        ];

        public const PASSWORD_MIN_LENGTH = 7;

        public const FEMALE = 'female';
        public const MALE = 'male';

        public const TYPE_SEX = [
            self::MALE => 'Muž',
            self::FEMALE => 'Žena',
        ];

        /**
         * @var string
         *
         * @ORM\Column(type="string")
         */
        protected string $firstName = '';

        /**
         * @var string
         *
         * @ORM\Column(type="string")
         */
        protected string $lastName = '';

        /**
         * @var string
         *
         * @ORM\Column(type="enum", columnDefinition="enum('male', 'female') DEFAULT 'male'", nullable=false)
         */
        protected string $sex = self::MALE;

        /**
         * @var string
         *
         * @ORM\Column(type="string")
         */
        protected string $phone = '';

        /**
         * @var string
         *
         * @ORM\Column(type="string", unique=true)
         */
        protected string $email = '';

        /**
         * @var string
         *
         * @ORM\Column(type="string")
         */
        protected string $password = '';

        /**
         * @var string
         *
         * @ORM\Column(type="string")
         */
        protected string $role = '';

        /**
         * @var null|string
         *
         * @ORM\Column(type="text", nullable=true)
         */
        protected ?string $resetToken = null;

        /**
         * @var null|DateTime
         *
         * @ORM\Column(type="datetime", nullable=true)
         */
        protected ?DateTime $activateOn = null;

        /**
         * @var null|DateTime
         *
         * @ORM\Column(type="datetime", nullable=true)
         */
        protected ?DateTime $blocked = null;

        /**
         * @var Image|null
         *
         * @ORM\ManyToOne(targetEntity="\App\Models\Image", cascade={"remove"})
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="image", referencedColumnName="id", onDelete="SET NULL", nullable=true)
         * })
         */
        protected ?Image $image = null;

        /**
         * @var Collection
         *
         * @ORM\OneToMany(targetEntity="\App\Models\Notification", mappedBy="user")
         */
        protected Collection $notifications;

        public function __construct()
        {
            $this->notifications = new ArrayCollection();
        }

        /**
         * @return string
         */
        public function getFirstname(): string
        {
            return $this->firstName;
        }

        /**
         * @param string $firstname
         * @return $this
         */
        public function setFirstname(string $firstname): self
        {
            $this->firstName = $firstname;

            return $this;
        }

        /**
         * @return string
         */
        public function getLastname(): string
        {
            return $this->lastName;
        }

        /**
         * @param string $lastname
         * @return $this
         */
        public function setLastname(string $lastname): self
        {
            $this->lastName = $lastname;

            return $this;
        }

        /**
         * @return string
         */
        public function getSex(): string
        {
            return $this->sex;
        }

        /**
         * @param string $sex
         * @return $this
         */
        public function setSex(string $sex): self
        {
            $this->sex = $sex;

            return $this;
        }

        /**
         * @return string
         */
        public function getPhone(): string
        {
            return $this->phone;
        }

        /**
         * @param string $phone
         * @return $this
         */
        public function setPhone(string $phone): self
        {
            $this->phone = $phone;

            return $this;
        }

        /**
         * @return string
         */
        public function getEmail(): string
        {
            return $this->email;
        }

        /**
         * @param string $email
         * @return $this
         */
        public function setEmail(string $email): self
        {
            $this->email = $email;

            return $this;
        }

        /**
         * @return string
         */
        public function getPassword(): string
        {
            return $this->password;
        }

        /**
         * @param string $password
         * @return $this
         */
        public function setPassword(string $password): self
        {
            $this->password = $password;

            return $this;
        }

        /**
         * @return string
         */
        public function getRole(): string
        {
            return $this->role;
        }

        /**
         * @param string $role
         * @return $this
         */
        public function setRole(string $role): self
        {
            $this->role = $role;

            return $this;
        }

        /**
         * @return string
         */
        public function getResetToken(): string
        {
            return $this->resetToken;
        }

        /**
         * @return string
         */
        public function generateResetToken(): string
        {
            $this->resetToken = md5(uniqid($this->email, true));

            return $this->resetToken;
        }

        /**
         * @return $this
         */
        public function cleanResetToken(): User
        {
            $this->resetToken = null;

            return $this;
        }

        /**
         * @return DateTime|null
         */
        public function getActivateOn(): ?DateTime
        {
            return $this->activateOn;
        }

        /**
         * @return bool
         */
        public function isActive(): bool
        {
            return (bool) $this->activateOn;
        }

        /**
         * @return $this
         */
        public function setActivateOn(): self
        {
            $this->activateOn = new DateTime();

            return $this;
        }

        /**
         * @return bool
         */
        public function isBlocked(): bool
        {
            return (bool) $this->blocked;
        }

        /**
         * @return DateTime|null
         */
        public function getBlocked(): ?DateTime
        {
            return $this->blocked;
        }

        /**
         * @param DateTime|null $blocked
         * @return $this
         */
        public function setBlocked(?DateTime $blocked): self
        {
            $this->blocked = $blocked;

            return $this;
        }

        /**
         * @return bool
         */
        public function hasImage(): bool
        {
            return $this->image !== null;
        }

        /**
         * @return Image|null
         */
        public function getImage(): ?Image
        {
            return $this->image;
        }

        /**
         * @param Image|null $image
         * @return $this
         */
        public function setImage(?Image $image): self
        {
            $this->image = $image;

            return $this;
        }

        /**
         * @param Deadpool $deadpool
         * @param int $size
         * @return string
         */
        public function getImageUrl(Deadpool $deadpool, int $size): string
        {
            if (! $this->image) {
                return '/assets/images/user-silhouette-bg.svg';
            }

            return $deadpool->image($this->image)->width($size)->height($size)->exact()->toString();
        }

        /**
         * @return string
         */
        public function getFullName(): string
        {
            if (! $this->firstName && ! $this->lastName) {
                return $this->email;
            }

            return implode(' ', array_filter([
                $this->firstName,
                $this->lastName
            ]));
        }

        /**
         * @param DI $di
         * @return callable|null
         */
        public function onStore(DI $di): ?callable
        {
            if (! $this->_isNew()) {
                return null;
            }

            return function () use ($di) {
                $di->get(AdminNotifications::class)->invokeRegisteredNewUser($this);
            };
        }
    }