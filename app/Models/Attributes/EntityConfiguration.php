<?php declare(strict_types=1);

namespace App\Models\Attributes;

use App\Utils\Arrays;
use Doctrine\ORM\Mapping as ORM;
use Nette\InvalidArgumentException;

trait EntityConfiguration
{
    /**
     * @var array|null
     *
     * @ORM\Column(type="json")
     */
    protected ?array $config = [];

    /**
     * @param string|null $property
     * @return array|mixed|null
     */
    private function getConfig(string $property = null): mixed
    {
        if ($property === null) {
            return $this->config;
        }

        try {
            return Arrays::get((array) $this->config, $property);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * @param $key
     * @param bool $default
     * @return bool
     */
    public function getBooleanConfig($key, bool $default = false): bool
    {
        $value = $this->getConfig($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return (bool) $value;
    }

    /**
     * @param $key
     * @param string $default
     * @return string
     */
    public function getStringConfig($key, string $default = ''): string
    {
        $value = $this->getConfig($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }

    /**
     * @param $key
     * @param int $default
     * @return int
     */
    public function getIntegerConfig($key, int $default = 0): int
    {
        $value = $this->getConfig($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }

    /**
     * @param $key
     * @param array $default
     * @return array
     */
    public function getArrayConfig($key, array $default = []): array
    {
        $value = $this->getConfig($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return (array) $value;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param string $property
     * @param $data
     * @return $this
     */
    public function addConfig(string $property, $data): self
    {
        $this->config[$property] = $data;

        return $this;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function mergeConfig(array $config): self
    {
        $this->config = array_merge((array) $this->config, $config);

        return $this;
    }
}
