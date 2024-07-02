<?php declare(strict_types=1);

namespace App\Utils;

use App\Models\MailMessage;
use Latte\Engine;

class MailMessageGenerator
{
    /**
     * @param string $recipient
     * @param string $body
     * @param string $title
     * @return MailMessage
     */
    public static function generate(string $recipient, string $body, string $title): MailMessage
    {
        return new MailMessage($recipient, self::generateHtml($body, $title), $title);
    }

    /**
     * @param string $body
     * @param string $title
     * @return string
     */
    private static function generateHtml(string $body, string $title): string
    {
        $latte = new Engine();
        $latte->setTempDirectory( DIR_TEMP . DS . 'cache' . DS . 'latte');
        return $latte->renderToString(DIR_APP . DS . 'Templates' . DS . 'mail.latte', [
            'body' => $body,
            'title' => $title,
        ]);
    }
}
