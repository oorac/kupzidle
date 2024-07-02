<?php declare(strict_types=1);

namespace App\Utils;

use Closure;
use App\Exceptions\NotFoundException;
use JsonSerializable;

final class LazyObject implements JsonSerializable
{
    /**
     * @var array
     */
    private array $values = [];

    /**
     * @var array
     */
    private array $callbacks = [];

    /**
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        foreach ($properties as $name => $value) {
            if ($value instanceof Closure) {
                $this->callbacks[$name] = $value;
                continue;
            }

            $this->values[$name] = $value;
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        foreach ($this->callbacks as $name => $callback) {
            if (! isset($this->values[$name])) {
                $this->values[$name] = $callback($this);
            }
        }

        return $this->values;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        if (! isset($this->values[$name])) {
            if (! isset($this->callbacks[$name])) {
                throw new NotFoundException(
                    'Lazy object does not contain property named `' . $name . '`. Available properties: `'
                    . implode('`, `', array_unique(
                        array_merge(array_keys($this->values), array_keys($this->callbacks)))
                    ) . '`.'
                );
            }

            $this->values[$name] = $this->callbacks[$name]($this);
        }

        return $this->values[$name];
    }

    /**
     * @param string $name
     * @param $value
     * @return void
     */
    public function set(string $name, $value): void
    {
        if ($value instanceof Closure) {
            $this->callbacks[$name] = $value;

            return;
        }

        $this->values[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param $value
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->values[$name]) || isset($this->callbacks[$name]);
    }
}
