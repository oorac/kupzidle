<?php declare(strict_types=1);

namespace App\Console;

use App\Services\Doctrine\EntityManager;
use App\Services\Money\ProductImportService;
use app\Services\RabbitMQ\AMQService;
use App\Utils\FileSystem;
use DirectoryIterator;
use Exception;
use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\ILogger;

/**
 * Split XML file with products from Money ERP
 */
class ProductXmlSplitterConsumerCommand extends Command
{
    /**
     * @var string
     */
    private string $name = 'app:split-product-xml';

    public function __construct(
        private readonly AMQService $rabbitMQService,
        private readonly ProductImportService $productImportService,
        private readonly ILogger $logger,
        private readonly EntityManager $entityManager,
        string $name = null
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
            ->setDescription('Split XML file with products from Money ERP');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $callback = function (){
            try {
                $this->productImportService->importData();
                foreach ($this->loadFiles() as $loadFile) {
                    $pathFile = ProductImportService::IMPORT_PRODUCT_DIR_SPLIT . $loadFile;
                    if (FileSystem::isFile($pathFile) && pathinfo($pathFile, PATHINFO_EXTENSION) === ProductImportService::EXTENSION_FILE) {
                        $json = json_encode(['path' => $pathFile, 'file' => $loadFile], JSON_THROW_ON_ERROR);
                        $this->rabbitMQService->sendMessage('processSplitXml', $json);
                    }
                }
                $this->entityManager->flush();
            } catch (Exception $exception) {
                $this->logger->log($exception, ILogger::ERROR);
            } finally {
                $this->entityManager->getConnection()->close();
            }
        };

        $this->rabbitMQService->consumeWithReconnect('splitProductXml', $callback);
        return Command::SUCCESS;
    }

    /**
     * @return Generator
     */
    private function loadFiles(): Generator
    {
        foreach (new DirectoryIterator(ProductImportService::IMPORT_PRODUCT_DIR_SPLIT) as $fileInfo) {
            if ($fileInfo->isFile()) {
                yield $fileInfo->getFilename();
            }
        }
    }

}

