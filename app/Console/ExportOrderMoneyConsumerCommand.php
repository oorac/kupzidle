<?php declare(strict_types=1);

namespace App\Console;

use App\Services\Money\ExportOrderService;
use app\Services\RabbitMQ\AMQService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\ILogger;


class ExportOrderMoneyConsumerCommand extends Command
{
    /**
     * @var string
     */
    private string $name = 'app:export-order-money';

    public function __construct(
        private readonly AMQService         $rabbitMQService,
        private readonly ExportOrderService $exportOrderService,
        private readonly ILogger            $logger,
        string                              $name = null
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
            ->setDescription('Export order to Money');
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
                $this->exportOrderService->createXmlFile();
                $this->exportOrderService->createOrderSupplier();
            } catch (Exception $exception) {
                $this->logger->log($exception, ILogger::ERROR);
            }
        };

        $this->rabbitMQService->consumeWithReconnect('exportOrderMoney', $callback);
        return Command::SUCCESS;
    }
}

