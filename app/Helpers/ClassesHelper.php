<?php declare(strict_types=1);

namespace App\Helpers;

use App\Utils\Arrays;
use ReflectionClass;
use ReflectionException;

class ClassesHelper
{
    /**
     * @return array
     */
    public static function getAll(): array
    {
        $classmap = array_keys(require DIR_VENDOR . DS . 'composer' . DS . 'autoload_classmap.php');

        return array_filter($classmap, static function (string $class) {
            return str_starts_with($class, 'App\\');
        });
    }

    /**
     * @param string $parent
     * @return array
     */
    public static function getSubclasses(string $parent): array
    {
        return array_filter(self::getAll(), static function (string $class) use ($parent) {
            try {
                return is_subclass_of($class, $parent, true) && ! (new ReflectionClass($class))->isAbstract();
            } catch (ReflectionException) {
                return false;
            }
        });
    }

    /**
     * @param string $class
     * @return string
     */
    public static function getShortName(string $class): string
    {
        return Arrays::last(explode('\\', $class));
    }
}
