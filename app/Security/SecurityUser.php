<?php declare(strict_types=1);

namespace App\Security;

use App\Models\User;
use Nette\Security\Authorizator;
use Nette\Security\User as NetteSecurityUser;

/**
 * @method Identity|null getIdentity()
 */
class SecurityUser extends NetteSecurityUser
{
    /**
     * Default role
     * @var string
     */
    public $guestRole = User::ROLE_GUEST;

    /**
     * @param UserStorage $storage
     * @param Authenticator|null $authenticator
     * @param Authorizator|null $authorizator
     */
    public function __construct(
        UserStorage $storage,
        Authenticator $authenticator = null,
        Authorizator $authorizator = null
    ) {
        parent::__construct(
            $storage,
            $authenticator,
            $authorizator
        );
    }

    /**
     * @return User|null
     */
    public function getIdentityUser(): ?User
    {
        if ($identity = $this->getIdentity()) {
            return $identity->getUser();
        }

        return null;
    }

    /**
     * @param User $user
     */
    public function smuggleIn(User $user): void
    {
        if ($identity = $this->getIdentity()) {
            $identity->setUser($user);
        }
    }
}
