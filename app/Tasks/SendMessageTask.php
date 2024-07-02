<?php declare(strict_types=1);

namespace App\Tasks;

use App\Services\Bunny\Task;
use App\Models\Repositories\MessageRepository;
use App\Providers\MessageSendersProvider;

class SendMessageTask extends AbstractTask
{
    /**
     * @param MessageRepository $messageRepository
     * @param MessageSendersProvider $messageSendersProvider
     */
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly MessageSendersProvider $messageSendersProvider
    ) {}

    /**
     * @param int $id
     * @return bool
     */
    public function run(int $id): bool
    {
        if (! $message = $this->messageRepository->find($id)) {
            return true;
        }

        if ($message->isSent()) {
            return true;
        }

        return $this->messageSendersProvider->resolveSenderByMessage($message)->send($message);
    }

    /**
     * @param int $id
     * @return Task
     */
    public static function create(int $id): Task
    {
        return parent::build(...func_get_args());
    }
}
