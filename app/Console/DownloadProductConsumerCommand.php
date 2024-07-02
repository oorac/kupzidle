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


class DownloadProductConsumerCommand extends Command
{
    /**
     * @var string
     */
    private string $name = 'app:download-products';

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
            ->setDescription('Download products from UpGates');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $callback = function ($msg) {
            $data = json_decode($msg->body, true, 512, JSON_THROW_ON_ERROR);
            try {
                if ($data['type'] === 'product') {
                    $this->upGatesService->getProducts();
                    $this->rabbitMQService->sendMessage('downloadProducts', json_encode(['type' => 'parameter'], JSON_THROW_ON_ERROR));
                    $this->rabbitMQService->sendMessage('downloadProducts', json_encode(['type' => 'label'], JSON_THROW_ON_ERROR));
                } elseif ($data['type'] === 'productAll') {
                    $this->upGatesService->getProducts(true);
                    $this->rabbitMQService->sendMessage('downloadProducts', json_encode(['type' => 'parameterAll'], JSON_THROW_ON_ERROR));
                    $this->rabbitMQService->sendMessage('downloadProducts', json_encode(['type' => 'labelAll'], JSON_THROW_ON_ERROR));
                } elseif ($data['type'] === 'parameter') {
                    $this->upGatesService->getParameters();
                } elseif ($data['type'] === 'parameterAll') {
                    $this->upGatesService->getParameters(true);
                } elseif ($data['type'] === 'label') {
                    $this->upGatesService->getLabels();
                } elseif ($data['type'] === 'labelAll') {
                    $this->upGatesService->getLabels(true);
                }
            } catch (Exception $exception) {
                $this->logger->log($exception, ILogger::ERROR);
            }
        };

        $this->rabbitMQService->consumeWithReconnect('downloadProducts', $callback);
        return Command::SUCCESS;
    }
}

