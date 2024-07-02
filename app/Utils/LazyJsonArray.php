<?php declare(strict_types=1);

namespace App\Utils;

class LazyJsonArray
{
    /**
     * @var string
     */
    private string $json;

    /**
     * @var array|null
     */
    private ?array $data = null;

    /**
     * @param string|null $json
     */
    public function __construct(?string $json = '{}')
    {
        $this->json = $json ?? '{}';
    }

    /**
     * @return string
     */
    public function getJson(): string
    {
        return $this->json;
    }

    /**
     * @return array
     */
    public function export(): array
    {
        if ($this->data === null) {
            $this->data = (array) json_decode($this->json, true);
        }

        return $this->data;
    }
}
