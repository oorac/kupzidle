<?php declare(strict_types = 1);

namespace App\Console;

use App\Services\ApplicationService;
use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleApplication extends Application
{
    /**
     * @param ApplicationService $applicationService
     * @param string $name
     * @param string $version
     */
    public function __construct(
        private readonly ApplicationService $applicationService,
        string $name = 'UNKNOWN',
        string $version = 'UNKNOWN'
    ) {
        parent::__construct($name, $version);
    }

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     * @throws Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $this->applicationService->onStartup();

        $autoExit = $this->isAutoExitEnabled();
        $this->setAutoExit(false);

        $exitCode = parent::run($input, $output);
        $exitCode = min($exitCode, 255);

        if ($autoExit) {
            exit($exitCode);
        }

        return $exitCode;
    }
}
