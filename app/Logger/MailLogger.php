<?php declare(strict_types=1);

namespace App\Logger;

use Tracy\Logger;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

class MailLogger extends Logger
{
    public $mailer;

    /**
     * @param string $logDir
     * @param SmtpMailer $mailer
     */
    public function __construct(string $logDir, SmtpMailer $mailer)
    {
        parent::__construct($logDir);
        $this->mailer = $mailer;
    }

    /**
     * @param $message
     * @param $level
     * @return void
     */
    public function log($message, $level = self::INFO): void
    {
        parent::log($message, $level);

        if ($level === self::ERROR) {
            $this->sendEmail($message);
        }
    }

    /**
     * @param $message
     * @return void
     */
    protected function sendEmail($message): void
    {
        $mail = new Message;
        $mail->setFrom('info@idop.cz')
            ->addTo('info@idop.cz')
            ->setSubject('Application Error')
            ->setBody("An error occurred in the application:\n\n" . $message);

        $this->mailer->send($mail);
    }
}
