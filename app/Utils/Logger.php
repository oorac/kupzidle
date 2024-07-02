<?php declare(strict_types=1);

namespace App\Utils;

use Bugsnag\Client;
use Bugsnag\Handler;
use Throwable;
use Tracy\BlueScreen;

class Logger extends \Tracy\Logger
{

    /**
     * @var Client
     */
    private Client $bugsnagClient;

    /**
     * @param string $directory
     * @param Client $bugsnagClient
     * @param string|null $email
     * @param BlueScreen|null $blueScreen
     */
    public function __construct(
        string $directory,
        Client $bugsnagClient,
        ?string $email = NULL,
        ?BlueScreen $blueScreen = NULL
    )
    {
        parent::__construct($directory, $email, $blueScreen);
        $this->bugsnagClient = $bugsnagClient;
        Handler::registerWithPrevious($this->bugsnagClient);
    }

    /**
     * @param mixed $message
     * @param mixed $level
     * @return string|null
     */
    public function log(mixed $message, $level = self::INFO): ?string
    {
        $isException = $message instanceof Throwable;
        $exceptionFile = \Tracy\Logger::log($message, $level);

        if ($isException) {
            $this->bugsnagClient->notifyException($message);
        }

        return $exceptionFile;
    }
}
