<?php declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use Nette\InvalidStateException;
use Nette\Security\Passwords;

class Password
{
    /**
     * @var Passwords
     */
    private Passwords $passwords;

    /**
     * @param Passwords $passwords
     */
    public function __construct(Passwords $passwords)
    {
        $this->passwords = $passwords;
    }

    /**
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function verify(User $user, string $password): bool
    {
        return $this->passwords->verify($password, $user->getPassword());
    }

    /**
     * @param string $password
     * @return string
     * @throws InvalidStateException
     */
    public function hash(string $password): string
    {
        return $this->passwords->hash($password);
    }
}
