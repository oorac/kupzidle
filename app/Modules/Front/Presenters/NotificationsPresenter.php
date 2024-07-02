<?php declare(strict_types=1);

namespace App\Modules\Front\Presenters;

use App\Services\NotificationsService;
use Nette\Application\AbortException;
use Nette\Application\Responses\JsonResponse;

final class NotificationsPresenter extends BasePresenter
{
    /**
     * @var NotificationsService
     * @inject
     */
    public NotificationsService $notificationsService;

    /**
     * @param int $limit
     * @param int|null $afterCursorID
     * @return never
     * @throws AbortException
     */
    public function actionFetch(int $limit = NotificationsService::DEFAULT_LIMIT, ?int $afterCursorID = null): never
    {
        $this->sendResponse(
            new JsonResponse(
                $this->notificationsService->fetch($limit, $afterCursorID)
            )
        );
    }

    /**
     * @return never
     * @throws AbortException
     */
    public function actionCount(): never
    {
        $this->sendResponse(
            new JsonResponse(
                $this->notificationsService->count()
            )
        );
    }
}
