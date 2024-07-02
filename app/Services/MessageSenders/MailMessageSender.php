<?php declare(strict_types=1);

    namespace App\Services\MessageSenders;

    use App\Exceptions\UnableToSendMessageException;
    use App\Models\Message;
    use App\Services\Doctrine\EntityManager;
    use Nette\Mail\Mailer;
    use Nette\Mail\Message as NetteMailMessage;
    use Nette\Mail\SendException;

    class MailMessageSender implements IMessageSender
    {
        /**
         * @param string $senderName
         * @param string $senderEmail
         * @param Mailer $mailer
         * @param EntityManager $entityManager
         */
        public function __construct(
            private readonly string $senderName,
            private readonly string $senderEmail,
            private readonly Mailer $mailer,
            private readonly EntityManager $entityManager,
        ) {}

        /**
         * @param Message $message
         * @return bool
         */
        public function send(Message $message): bool
        {
            return true;
            try {
                $mail = (new NetteMailMessage)
                    ->setFrom(sprintf(
                        '%s <%s>',
                        $this->senderName,
                        $this->senderEmail,
                    ))
                    ->addTo($message->getRecipient())
                    ->setSubject($message->getTitle())
                    ->setHtmlBody($message->getBody());

                $this->mailer->send($mail);

                $message->markSend();

                $this->entityManager->flush();

                return true;
            } catch (SendException $e) {
                throw (new UnableToSendMessageException('Unable to send mail message'))
                    ->addData('message', $message)
                    ->addData('mail', $mail ?? null)
                    ->addData('previousException', $e);
            }
        }
    }
