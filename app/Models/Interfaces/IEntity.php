<?php declare(strict_types=1);

namespace App\Models\Interfaces;

interface IEntity
{
    /**
     * @return int|null
     * @internal
     */
    public function getId(): ?int;

    /**
     * @return string
     * @internal
     */
    public static function _getEntityClassName(): string;

    /**
     * @return string
     * @internal
     */
    public static function _getEntityShortClassName(): string;

    /**
     * @return array
     * @internal
     */
    public function _getEntityProperties(): array;

    /**
     * @param string $property
     * @return mixed
     * @internal
     */
    public function _getEntityProperty(string $property): mixed;

    /**
     * @param string $property
     * @param $value
     * @internal
     */
    public function _setEntityProperty(string $property, $value): void;
}
