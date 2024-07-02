<?php

    namespace App\Services;
    use Nette\Mail\Mailer;
    use Nette\Mail\Message;
    use Nette\Mail\SmtpMailer;
    use Nette\Neon\Exception;
    use Tracy\Debugger;

    class MailerService
    {

        public function __construct()
        {
            
        }
//        public function sendMail($emailTo, $emailFrom, $message = '', $subject = '', $htmlBody = [], $attach = ''): bool
//        {
//            try {
//                $mail = new Message();
//                $mail->setFrom($emailFrom)
//                    ->addTo($emailTo);
//                if (!empty($attach)) {
//                    $mail->addAttachment($attach);
//                }
//                if (empty($htmlBody)) {
//                    $mail->setSubject($subject)
//                        ->setBody($message);
//                } else {
////                    $latte = new Engine;
////                    $web = $this->pageRepository->getPageInfoAssoc();
////                    $params = array(
////                        'companyName' => $web['company_name'],
////                        'namePage' => $web['title'],
////                        'streetPage' => $web['street'],
////                        'cityPage' => $web['city'],
////                        'zipcodePage' => $web['zipcode'],
////                        'statePage' => $web['state'],
////                        'basePath' => $this->appDir,
////                        'imagePath' => $htmlBody['imagePath'],
////                    );
////                    foreach ($htmlBody['params'] as $key => $param) {
////                        $params[$key] = $param;
////                    }
////                    $mail->setHtmlBody($latte->renderToString($htmlBody['templatesPath'] . $htmlBody['latte'], $params), $htmlBody['imagePath']);
//                }
//                $mailer = new SmtpMailer([
//                    'host' => 'wes1-smtp.wedos.net',
//                    'username' => 'info@idop.cz',
//                    'password' => 'DO62prla_*7410',
//                    'secure' => 'tls',
//                    'port' => '587',
//                ]);
//                $mailer->send($mail);
//                return true;
//            } catch (Exception $e) {
//                Debugger::log($e, Debugger::ERROR);
//                return false;
//            }
//        }
    }