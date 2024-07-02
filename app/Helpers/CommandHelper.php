<?php declare(strict_types=1);

namespace App\Helpers;

use App\Utils\Logger;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandHelper
{
    /**
     * @param OutputInterface $output
     * @return Logger
     */
    public static function getLogger(OutputInterface $output): Logger
    {
        return new Logger(static function (string $row, string $type = 'info') use ($output) {
            $output->writeln('<' . $type . '>' . $row . '</' . $type . '>');
        });
    }
}