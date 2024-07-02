<?php declare(strict_types=1);

namespace App\Models\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait EntityID
{
	/**
	 * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected ?int $id = null;

    /**
     * @return int|null
     */
	public function getId(): ?int
	{
		return $this->id;
	}

    /**
     * @return void
     */
	public function __clone()
	{
		$this->id = null;

		if (isset($this->extId)) {
            $this->extId = null;
        }
	}
}
