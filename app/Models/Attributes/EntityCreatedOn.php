<?php declare(strict_types=1);

namespace App\Models\Attributes;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait EntityCreatedOn
{
    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected ?DateTime $createdOn = null;

    /**
     * @return DateTime
     */
    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedOnNow(): void
    {
        $this->createdOn = new DateTime();
    }
}
