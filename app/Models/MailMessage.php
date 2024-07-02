<?php declare(strict_types=1);

    namespace App\Models;

    use App\Models\Attributes\Entity;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="App\Models\Repositories\MessageRepository")
     */
    class MailMessage extends Message
    {
        use Entity;
    }
