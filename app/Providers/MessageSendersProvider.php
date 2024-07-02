<?php declare(strict_types=1);

namespace App\Providers;

use App\Models\Message;
use App\Models\MailMessage;
use App\Services\DI;
use App\Services\MessageSenders\IMessageSender;
use App\Services\MessageSenders\MailMessageSender;
use RuntimeException;

class MessageSendersProvider
{
    /**
     * @param DI $di
     */
    public function __construct(private readonly DI $di) {}

    /**
     * @param Message $message
     * @return IMessageSender
     */
    public function resolveSenderByMessage(Message $message): IMessageSender
    {
        if ($message instanceof MailMessage) {
            return $this->di->get(MailMessageSender::class);
        }

        throw new RuntimeException('Sender not found for message of type `' . $message::class . '`');
    }
}
