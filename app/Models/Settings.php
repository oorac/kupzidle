<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use App\Models\Attributes\EntityCreatedOn;
    use App\Models\Attributes\EntityID;
    use App\Models\Interfaces\IEntity;
    use App\Models\Interfaces\IEntityOnStoreCallback;
    use App\Services\DI;
    use App\Services\Doctrine\EntityManager;
    use App\Services\HistoricalService;
    use Doctrine\ORM\Mapping as ORM;
    use Nette\Security\User as UserEntity;

    /**
     * @ORM\Entity(repositoryClass="\App\Models\Repositories\SettingsRepository")
     * @ORM\Table(name="settings")
     * @ORM\HasLifecycleCallbacks
     */
    class Settings implements IEntity, IEntityOnStoreCallback
    {
        use Entity;
        use EntityID;
        use EntityCreatedOn;

        public const TYPE_NUMBER = 'NUMBER';
        public const TYPE_TEXT = 'TEXT';
        public const LIST_TYPES = [
            self::TYPE_NUMBER => 'Číselná hodnota',
            self::TYPE_TEXT => 'Textová hodnota'
        ];

        /**
         * @var string
         *
         * @ORM\Column(type="text", nullable=false)
         */
        protected string $title;

        /**
         * @var string
         *
         * @ORM\Column(type="text", nullable=false)
         */
        protected string $type;

        /**
         * @var string
         *
         * @ORM\Column(type="text", nullable=false)
         */
        protected string $value;

        /**
         * @var bool
         *
         * @ORM\Column(type="boolean", nullable=false)
         */
        protected bool $defaultValue = false;

        /**
         * @param string $title
         * @param string $type
         * @param string $value
         */
        public function __construct(string $title, string $type, string $value)
        {
            $this->title = $title;
            $this->type = $type;
            $this->value = $value;
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
         * @return $this
         */
        public function setTitle(string $title): self
        {
            $this->title = $title;

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
         * @return $this
         */
        public function setType(string $type): self
        {
            $this->type = $type;

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
         * @return $this
         */
        public function setValue(string $value): self
        {
            $this->value = $value;

            return $this;
        }

        /**
         * @return bool
         */
        public function isDefault(): bool
        {
            return $this->defaultValue;
        }

        /**
         * @param bool $defaultValue
         * @return $this
         */
        public function setDefault(bool $defaultValue): self
        {
            $this->defaultValue = $defaultValue;

            return $this;
        }

        /**
         * @param DI $di
         * @return callable|null
         */
        public function onStore(DI $di): ?callable
        {
            $entityManager = $di->get(EntityManager::class);
            $user = $di->get(UserEntity::class);
            $historicalService = new HistoricalService($entityManager);

            if ($this->_isNew()) {

                return function () use ($historicalService, $user) {
                    $historicalService->addSettingsHistory(Historical::ACTION_CREATE, $this, $user->getIdentityUser());
                };
            }

            return function () use ($historicalService, $user) {
                $historicalService->addSettingsHistory(Historical::ACTION_UPDATE, $this, $user->getIdentityUser());
            };
        }
    }