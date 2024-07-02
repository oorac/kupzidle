<?php declare(strict_types=1);

namespace App\Models\Attributes;

use App\Exceptions\NotFoundException;
use App\Utils\Arrays;

trait Entity
{
    /**
     * @return array
     * @internal
     */
    public function _getEntityProperties(): array
    {
        $properties = [];
        foreach (get_object_vars($this) as $property => $value) {
            if (! str_starts_with($property, '__')) {
                $properties[$property] = $value;
            }
        }

        return $properties;
    }

    /**
     * @param string $property
     * @return mixed
     * @internal
     */
    public function _getEntityProperty(string $property): mixed
    {
        if (! array_key_exists($property, get_object_vars($this))) {
            throw new NotFoundException(
                sprintf('Property "%s:$%s" cannot be found.', $property, get_class($this))
            );
        }

        return $this->$property;
    }

    /**
     * @param string $property
     * @param $value
     * @internal
     */
    public function _setEntityProperty(string $property, $value): void
    {
        if (! array_key_exists($property, get_object_vars($this))) {
            throw new NotFoundException(
                sprintf('Property "%s:$%s" cannot be found.', $property, get_class($this))
            );
        }

        $this->$property = $value;
    }

    /**
     * @return bool
     * @internal
     */
    public function _isNew(): bool
    {
        return $this->_getEntityProperty('id') === null;
    }

    /**
     * @return string
     * @internal
     */
    public static function _getEntityClassName(): string
    {
        return self::class;
    }

    /**
     * @return string
     * @internal
     */
    public static function _getEntityShortClassName(): string
    {
        return Arrays::last(explode('\\', self::class));
    }
}
