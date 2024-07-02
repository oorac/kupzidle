<?php declare(strict_types=1);

namespace App\Console;

use app\Services\RabbitMQ\AMQService;
use App\Services\UpGates\UpGatesService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\ILogger;


class DownloadCategoryConsumerCommand extends Command
{
    /**
     * @var string
     */
    private string $name = 'app:download-categories';

    public function __construct(
        private readonly UpGatesService $upGatesService,
        private readonly AMQService     $rabbitMQService,
        private readonly ILogger        $logger,
        string                          $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName($this->name)
            ->setDescription('Download categories from UpGates');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $callback = function () {
            try {
                $this->upGatesService->getCategories();
            } catch (Exception $exception) {
                $this->logger->log($exception, ILogger::ERROR);
            }
        };

        $this->rabbitMQService->consumeWithReconnect('downloadCategories', $callback);
        return Command::SUCCESS;
    }
}

