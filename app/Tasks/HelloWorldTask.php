<?php declare(strict_types=1);

namespace App\Tasks;

use App\Services\Bunny\Task;

class HelloWorldTask extends AbstractTask
{
    /**
     * @param string $message
     * @return bool
     */
    public function run(string $message): bool
    {
        return (bool) file_put_contents(DIR_LOG . 'HelloWorldTask.log', $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * @param string $message
     * @return Task
     */
    public static function create(string $message): Task
    {
        return parent::build(...func_get_args());
    }
}
