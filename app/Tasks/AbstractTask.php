<?php declare(strict_types=1);

namespace App\Tasks;

use App\Services\Bunny\Task;

abstract class AbstractTask implements ITask
{
    /**
     * @return Task
     */
    protected static function build(): Task
    {
        return new Task(static::class, func_get_args());
    }
}
