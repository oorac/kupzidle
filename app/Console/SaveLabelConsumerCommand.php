<?php declare(strict_types=1);

namespace App\Console;

use App\Services\ProductService;
use app\Services\RabbitMQ\AMQService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\ILogger;


class SaveLabelConsumerCommand extends Command
{
    /**
     * @var string
     */
    private string $name = 'app:save-label';

    public function __construct(
        private readonly ProductService $productService,
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
            ->setDescription('Save label from UpGates');
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
                $this->productService->saveLabel($data['data']);
            } catch (Exception $exception) {
                $this->logger->log($exception, ILogger::ERROR);
            }
        };

        $this->rabbitMQService->consumeWithReconnect('saveLabel', $callback, 20);
        return Command::SUCCESS;
    }
}

