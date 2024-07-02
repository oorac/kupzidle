<?php declare(strict_types=1);

namespace App\Security;

use App\Exceptions\AuthenticationException;
use App\Models\Repositories\UserRepository;
use App\Services\User\Password;
use Nette\Security\Authenticator as LegacyAuthenticator;
use Nette\Security\IIdentity;

class Authenticator implements LegacyAuthenticator
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var Password
     */
    private Password $password;

    /**
     * @param UserRepository $userRepository
     * @param Password $password
     */
    public function __construct(
        UserRepository $userRepository,
        Password $password
    ) {
        $this->userRepository = $userRepository;
        $this->password = $password;
    }

    /**
     * @param string $user
     * @param string $password
     * @return IIdentity
     * @throws AuthenticationException
     */
    public function authenticate(string $user, string $password): IIdentity
    {
        $entity = $this->userRepository->findOneBy([
            'email' => $user,
        ]);

        if (! $entity) {
            throw new AuthenticationException('E-mail není registrován');
        }

        if (! $this->password->verify($entity, $password)) {
            throw new AuthenticationException('Nesprávné heslo');
        }

        return new Identity($entity);
    }
}
