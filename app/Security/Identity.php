<?php declare(strict_types=1);

namespace App\Security;

use App\Models\User;
use App\Services\Doctrine\EntityManager;
use Nette\Security\IIdentity;

/**
 * @method array getData()
 */
class Identity implements IIdentity
{
    /**
     * @var array
     */
    protected array $roles = [];

    /**
     * @var int
     */
    protected int $userId;

    /**
     * @var User|null
     */
    protected ?User $user = null;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->setUser($user);
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        $this->userId = (int) $user->getId();
        $this->roles = [$user->getRole()];

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->userId;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param EntityManager $entityManager
     * @return $this
     */
    public function load(EntityManager $entityManager): self
    {
        if ($this->user) {
            return $this;
        }

        if ($this->userId) {

            /** @var User $user */
            $user = $entityManager->getRepository(User::class)->find($this->userId);

            if ($user) {
                $this->setUser($user);
                $entityManager->flush();
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['userId'];
    }
}
