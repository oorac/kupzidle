<?php declare(strict_types=1);

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EnumType extends Type
{
    /**
     * @var string
     */
    protected string $name = 'enum';

    /**
     * @var array
     */
    protected array $values = [];

    /**
     * @param array $column
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $values = array_map(static function ($val) {
            return "'" . $val . "'";
        }, $this->values);

        return "ENUM(" . implode(", ", $values) . ")";
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param AbstractPlatform $platform
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}