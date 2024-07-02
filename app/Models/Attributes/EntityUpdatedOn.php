<?php declare(strict_types=1);

namespace App\Models\Attributes;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait EntityUpdatedOn
{
    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected ?DateTime $updatedOn = null;

    /**
     * @return DateTime
     */
    public function getUpdatedOn(): DateTime
    {
        return $this->updatedOn;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedOnNow(): void
    {
        $this->updatedOn = new DateTime();
    }
}
