<?php declare(strict_types=1);

namespace App\Console;

use App\Services\Money\ProductImportService;
use app\Services\RabbitMQ\AMQService;
use App\Utils\FileSystem;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\ILogger;

/**
 * Load split XML file with products from Money ERP
 */
class ProductXmlProcessorConsumerCommand extends Command
{
    /**
     * @var string
     */
    private string $name = 'app:process-split-xml';

    public function __construct(
        private readonly AMQService           $rabbitMQService,
        private readonly ProductImportService $productImportService,
        private readonly ILogger              $logger,
        string                                $name = null
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
            ->setDescription('Load split XML file with products from Money ERP');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $callback = function ($msg){
            $data = json_decode($msg->body, true, 512, JSON_THROW_ON_ERROR);
            try {
                $this->productImportService->saveData($data['path']);

//                $dir = ProductImportService::IMPORT_PRODUCT_DIR_SPLIT_BACKUP . (new DateTime())->format('Y_m_d') . DS;
//                if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
//                    $this->logger->log(
//                        new \RuntimeException(sprintf('Directory "%s" was not created', $dir))
//                    );
//                }

                FileSystem::delete($data['path']);
            } catch (Exception $exception) {
                $this->logger->log($exception, ILogger::ERROR);
            }
        };

        $this->rabbitMQService->consumeWithReconnect('processSplitXml', $callback);
        return Command::SUCCESS;
    }
}

