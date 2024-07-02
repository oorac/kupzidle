<?php declare(strict_types=1);

namespace App\Console;

use app\Services\RabbitMQ\AMQService;
use App\Services\UpGates\UpGatesService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SaveOrderConsumerCommand extends Command
{
    /**
     * @var string
     */
    private string $name = 'app:save-order';

    public function __construct(
        private readonly UpGatesService $upGatesService,
        private readonly AMQService     $rabbitMQService,
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
            ->setDescription('Save order from UpGates');
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
            $this->upGatesService->processOrder($data['data']);
        };

        $this->rabbitMQService->consumeWithReconnect('saveOrder', $callback);
        return Command::SUCCESS;
    }
}

