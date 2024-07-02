<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Attributes\Entity;
use App\Models\Attributes\EntityCreatedOn;
use App\Models\Attributes\EntityID;
use App\Models\Interfaces\IEntity;
use App\Models\Interfaces\IEntityOnStoreCallback;
use App\Providers\SettingsProvider;
use App\Services\DI;
use App\Services\Doctrine\EntityManager;
use App\Utils\MailMessageGenerator;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\App\Models\Repositories\NotificationRepository")
 * @ORM\Table(name="notification")
 * @ORM\HasLifecycleCallbacks
 */
class Notification implements IEntity, IEntityOnStoreCallback
{
    use Entity;
    use EntityID;
    use EntityCreatedOn;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\App\Models\User", inversedBy="notifications")
     * @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="CASCADE")
     */
    protected User $user;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected string $content = '';

    /**
     * @var DateTime|null
     *
     * @ORM\Column(nullable=true)
     */
    protected ?DateTime $sentOn = null;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(nullable=true)
     */
    protected ?DateTime $seenOn = null;

    /**
     * @param User $user
     * @param string $content
     */
    public function __construct(User $user, string $content)
    {
        $this->user = $user;
        $this->content = $content;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
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
    public function setSentOnNow(): self
    {
        $this->sentOn = new DateTime();

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getSeenOn(): ?DateTime
    {
        return $this->seenOn;
    }

    /**
     * @return $this
     */
    public function setSeenOnNow(): self
    {
        $this->seenOn = new DateTime();

        return $this;
    }

    /**
     * @param DI $di
     * @return callable|null
     */
    public function onStore(DI $di): ?callable
    {
        if ($this->_isNew() && ! $this->getUser()->isActive()) {
            $di->get(EntityManager::class)->persist(MailMessageGenerator::generate(
                $this->getUser()->getEmail(),
                $this->content,
                'Nová notifikace z webu ' . $di->get(SettingsProvider::class)->getString('siteName')
            ));
        }

        return null;
    }
}
