<?php declare(strict_types=1);

namespace App\Providers;

use App\Exceptions\ValidationException;
use App\Utils\Arrays;
use Nette\InvalidArgumentException;

class SettingsProvider
{
    /**
     * @var self
     */
    private static self $instance;

    /**
     * @var array
     */
    private array $settings;

    /**
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
        static::$instance = $this;
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        return static::$instance;
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null): mixed
    {
        try {
            $value = Arrays::get($this->settings, $key);
        } catch (InvalidArgumentException) {}

        return $value ?? $default;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function require($key): mixed
    {
        $value = $this->get($key);
        if ($value === null) {
            throw new ValidationException('Missing `' . (is_array($key) ? implode('.', $key) : $key) . '` configuration!');
        }

        return $value;
    }

    /**
     * @param $key
     * @param null $default
     * @return string
     */
    public function getString($key, $default = null): string
    {
        return (string) $this->get($key, $default);
    }

    /**
     * @param $key
     * @param $default
     * @return int
     */
    public function getInteger($key, $default = null): int
    {
        return (int) $this->get($key, $default);
    }

    /**
     * @param $key
     * @param $default
     * @return float
     */
    public function getFloat($key, $default = null): float
    {
        return (float) $this->get($key, $default);
    }

    /**
     * @param $key
     * @param $default
     * @return bool
     */
    public function getBoolean($key, $default = null): bool
    {
        return (bool) $this->get($key, $default);
    }

    /**
     * @param $key
     * @param $default
     * @return array
     */
    public function getArray($key, $default = null): array
    {
        $array = (array) $this->get($key, $default);

        return Arrays::isList($array) ? array_unique($array) : $array;
    }
}
