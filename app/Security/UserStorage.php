<?php declare(strict_types=1);

namespace App\Security;

use App\Services\Doctrine\EntityManager;
use Exception;
use Nette\Http\Session;
use Nette\Http\UserStorage as BaseUserStorage;
use Nette\Security\IIdentity;

class UserStorage extends BaseUserStorage
{
    /**
     * @param Session $session
     * @param EntityManager $entityManager
     */
    public function __construct(Session $session, private readonly EntityManager $entityManager)
    {
        parent::__construct($session);
    }

    /**
     * @return IIdentity|null
     * @throws Exception
     */
    public function getIdentity(): ?IIdentity
    {
        /** @var Identity $identity */
        if ($identity = parent::getIdentity()) {
            return $identity->load($this->entityManager);
        }

        return null;
    }
}
