<?php declare(strict_types=1);

use Tracy\Debugger;

/**
 * @param string|int|float|bool|array|object|null $content
 * @return void
 */
function file_log(null|string|int|float|bool|array|object $content): void
{
    if ($content === null) {
        $content = '[NULL] > NULL';
    } elseif (is_string($content)) {
        $content = '[string, length: ' . strlen($content) . '] > ' . $content;
    } elseif (is_int($content)) {
        $content = '[int] > ' . $content;
    } elseif (is_float($content)) {
        $content = '[float] > ' . $content;
    } elseif (is_bool($content)) {
        $content = '[boolean] > ' . ($content ? 'TRUE' : 'FALSE');
    } elseif (is_array($content)) {
        $content = '[array] > ' . json_encode($content);
    } elseif (is_object($content)) {
        if ($content instanceof Stringable) {
            $content = '[object, stringable] > ' . $content;
        } else {
            $content = '[object, serialized] > ' . serialize($content);
        }
    }

    $data = '[' . date('Y-m-d H:i:s') . ']'
        . ' '
        . '[' . PHP_SAPI . ']'
        . ' '
        . '[' . (int) getmypid() . ']'
        . ' '
        . $content
        . PHP_EOL;

    file_put_contents(DIR_LOG . '/log.log', $data, FILE_APPEND);
}

if (! function_exists('fdump')) {
    /**
     * @param mixed ...$args
     * @tracySkipLocation
     */
    function fdump(...$args): void
    {
        Debugger::$productionMode = false;
        dump(...$args);
    }
}

if (! function_exists('fdumpe')) {
    /**
     * @param mixed ...$args
     * @tracySkipLocation
     */
    function fdumpe(...$args): void
    {
        Debugger::$productionMode = false;
        dumpe(...$args);
    }
}