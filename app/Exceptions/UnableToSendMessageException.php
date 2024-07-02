<?php declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class UnableToSendMessageException extends RuntimeException
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @param string $property
     * @param $value
     * @return $this
     */
    public function addData(string $property, $value): self
    {
        $this->data[$property] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
