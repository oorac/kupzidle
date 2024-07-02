<?php declare(strict_types=1);

namespace App\Console;

use App\Services\FeedService;
use app\Services\RabbitMQ\AMQService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\ILogger;


class GenerateFeedConsumerCommand extends Command
{
    /**
     * @var string
     */
    private string $name = 'app:generate-feed';

    public function __construct(
        private readonly FeedService $feedService,
        private readonly AMQService  $rabbitMQService,
        private readonly ILogger     $logger,
        string                       $name = null
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
            ->setDescription('Generate feed');
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
//            try {
                $this->feedService->generate();
//            } catch (Exception $e) {
//                $this->logger->log(
//                    new Exception('Došlo k chybě:' . $e->getMessage()),
//                    ILogger::CRITICAL
//                );
//            }
        };

        $this->rabbitMQService->consumeWithReconnect('generateFeed', $callback);
        return Command::SUCCESS;
    }
}

