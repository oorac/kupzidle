<?php declare(strict_types=1);

namespace App\Services\MessageSenders;

use App\Models\Message;

interface IMessageSender
{
    /**
     * @param Message $message
     * @return bool
     */
    public function send(Message $message): bool;
}
