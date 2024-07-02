<?php declare(strict_types=1);

namespace App\Doctrine\DBAL\Types;

use App\Utils\LazyJsonArray;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use function is_resource;
use function stream_get_contents;

/**
 * Type that maps a PHP array to a clob SQL type.
 */
class LazyJsonArrayType extends Type
{
    private const NAME = 'lazy_json_array';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'LONGTEXT';
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return json_encode($value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $value = is_resource($value) ? stream_get_contents($value) : $value;

        return new LazyJsonArray($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }
}
