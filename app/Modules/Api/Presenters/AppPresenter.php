<?php declare(strict_types=1);

namespace App\Modules\Api\Presenters;

use App\Services\Doctrine\EntityManager;
use App\Services\Money\ExportOrderService;
use App\Services\Money\ProductImportService;
use app\Services\RabbitMQ\AMQService;
use App\Services\UpGates\UpGatesService;
use App\Utils\FileSystem;
use DateTime;
use Exception;
use Nette\Application\AbortException;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;

final class AppPresenter extends Presenter
{
    /**
     * @var EntityManager
     * @inject
     */
    public EntityManager $entityManager;

    /**
     * @var AMQService
     * @inject
     */
    public AMQService $rabbitMQService;

    /**
     * @var UpGatesService
     * @inject
     */
    public UpGatesService $upGatesService;

    /**
     * @var ProductImportService
     * @inject
     */
    public ProductImportService $productImportService;

    /**
     * @var ExportOrderService
     * @inject
     */
    public ExportOrderService $exportOrderService;

    /**
     * Function for testing
     *
     * @return void
     * @throws AbortException
     */
    public function actionTest(): void
    {
        $this->upGatesService->getOrders();
        exit;
        try {
            foreach ($this->loadFiles() as $loadFile) {
                $pathFile = ProductImportService::IMPORT_PRODUCT_DIR_SPLIT . $loadFile;
                if (FileSystem::isFile($pathFile) && pathinfo($pathFile, PATHINFO_EXTENSION) === ProductImportService::EXTENSION_FILE) {
                    $this->productImportService->saveData($pathFile);
                    $dir = ProductImportService::IMPORT_PRODUCT_DIR_SPLIT_BACKUP . (new DateTime())->format('Y_m_d') . DS;
                    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                        $a = 1;
                    }

                    FileSystem::move($pathFile, $dir . $loadFile);
                }
            }
        } catch (Exception $exception) {
            $b = 1;
        }

        $this->sendResponse(new TextResponse('OK'));
    }

    /**
     * Load XML file with products from Money ERP
     *
     * @return void
     * @throws AbortException
     */
    public function actionProductXmlSplitter(): void
    {
        $this->rabbitMQService->sendMessage('splitProductXml', json_encode([], JSON_THROW_ON_ERROR));

        $this->entityManager->flush();

        $this->sendResponse(new TextResponse('OK'));
    }

    /**
     * Download products from UpGates
     * @return void
     * @throws AbortException
     */
    public function actionDownloadProduct(): void
    {
        $this->rabbitMQService->sendMessage('downloadProducts', json_encode(['type' => 'product'], JSON_THROW_ON_ERROR));

        $this->entityManager->flush();

        $this->sendResponse(new TextResponse('OK'));
    }

    /**
     * Download products from UpGates
     * @return void
     * @throws AbortException
     */
    public function actionDownloadProductAll(): void
    {
        $this->rabbitMQService->sendMessage('downloadProducts', json_encode(['type' => 'productAll'], JSON_THROW_ON_ERROR));

        $this->entityManager->flush();

        $this->sendResponse(new TextResponse('OK'));
    }

    /**
     * Download categories from UpGates
     * @return void
     * @throws AbortException
     */
    public function actionDownloadCategory(): void
    {
        $this->rabbitMQService->sendMessage('downloadCategories', json_encode(['type' => 'category'], JSON_THROW_ON_ERROR));

        $this->entityManager->flush();

        $this->sendResponse(new TextResponse('OK'));
    }

    /**
     * Download orders from UpGates
     *
     * @return void
     * @throws AbortException
     */
    public function actionDownloadOrder(): void
    {
        $this->rabbitMQService->sendMessage('downloadOrders', json_encode(['type' => 'order'], JSON_THROW_ON_ERROR));

        $this->entityManager->flush();

        $this->sendResponse(new TextResponse('OK'));
    }

    /**
     * Sync with Money
     * @return void
     * @throws AbortException
     */
    public function actionExportOrders(): void
    {
        $this->rabbitMQService->sendMessage('exportOrderMoney', json_encode(['type' => 'orders'], JSON_THROW_ON_ERROR));

        $this->entityManager->flush();

        $this->sendResponse(new TextResponse('OK'));
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function actionGenerateLuigisboxFeed(): void
    {
        $this->rabbitMQService->sendMessage('generateFeed', json_encode(['type' => 'product'], JSON_THROW_ON_ERROR));

        $this->entityManager->flush();

        $this->sendResponse(new TextResponse('OK'));
    }

    /**
     * @return array
     */
    private function loadFiles(): array
    {
        return FileSystem::scanDir(ProductImportService::IMPORT_PRODUCT_DIR_SPLIT);
    }
}
