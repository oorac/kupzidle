<?php declare(strict_types=1);

namespace App\Models\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait EntityExtID
{
	/**
	 * @var string|null
     *
     * @ORM\Column(type="string", length=32, nullable=true)
	 */
	protected ?string $extId = null;

    /**
     * @return string|null
     */
	public function getExtId(): ?string
	{
		return $this->extId;
	}

    /**
     * @param $extId
     * @return $this
     */
    public function setExtId($extId)
    {
        $this->extId = $extId ? (string) $extId : null;

        return $this;
    }
}
