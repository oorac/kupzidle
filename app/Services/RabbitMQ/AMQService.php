<?php declare(strict_types=1);

namespace App\Services\RabbitMQ;

use Closure;
use ErrorException;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Tracy\ILogger;

class AMQService
{
    const WAIT_BEFORE_RECONNECT_uS = 1000000;

    /**
     * @var ILogger
     * @inject
     */
    public ILogger $logger;

    /**
     * @var AMQProxy
     * @inject
     */
    public AMQProxy $amqProxy;

    /**
     * @var array
     */
    private array $settings;

    /**
     * @param array $settings
     * @param ILogger $logger
     * @throws Exception
     */
    public function __construct(array $settings, ILogger $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;
        $this->connect();
    }


    /**
     * Initializes the connection and channel.
     *
     * @throws Exception
     */
    private function connect(): void
    {
        $connection = null;
        try {
            $connection = new AMQPStreamConnection(
                $this->settings['host'],
                $this->settings['port'],
                $this->settings['user'],
                $this->settings['password'],
                $this->settings['vhost']
            );
            $this->amqProxy = new AMQProxy($connection);
        }  catch(AMQPRuntimeException $e) {
            echo $e->getMessage();
            $this->cleanup_connection($connection);
            usleep(self::WAIT_BEFORE_RECONNECT_uS);
        } catch(\RuntimeException|ErrorException $e) {
            $this->cleanup_connection($connection);
            usleep(self::WAIT_BEFORE_RECONNECT_uS);
        } catch (Exception $exception) {
            $this->logger->log($exception, ILogger::ERROR);
            return;
        }
    }

    /**
     * @param AMQPStreamConnection|null $connection
     * @return void
     */
    function cleanup_connection(?AMQPStreamConnection $connection = null): void
    {
        try {
            $connection?->close();
        } catch (ErrorException|Exception $e) {
        }
    }

    /**
     * @param string $queue
     * @param string $message
     * @return void
     * @throws Exception
     */
    public function sendMessage(string $queue, string $message): void
    {
        try {
            $channel = $this->amqProxy->getChannel();
            $channel->queue_declare($queue, false, true, false, false);
            $msg = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
            $channel->basic_publish($msg, '', $queue);
        } catch (Exception $exception) {
            $this->logger->log($exception, ILogger::ERROR);
        }
    }

    /**
     * @param string $queue
     * @param Closure $callback
     * @param int $prefetchCount
     * @return void
     * @throws Exception
     */
    public function consumeWithReconnect(string $queue, Closure $callback, int $prefetchCount = 1): void
    {
        while (true) {
            try {
                $this->consume($queue, $callback, $prefetchCount);
                break;
            } catch (Exception $e) {
                $this->logger->log($e, ILogger::ERROR);
                sleep(10);
                $this->amqProxy->reconnect();
            }
        }
    }

    /**
     * @param string $queue
     * @param Closure $callback
     * @param int $prefetchCount
     * @return void
     * @throws Exception
     */
    public function consume(string $queue, Closure $callback, int $prefetchCount = 1): void
    {
        $channel = $this->amqProxy->getChannel();
        $channel->queue_declare($queue, false, true, false, false);
        $channel->basic_qos(null, $prefetchCount, false);
        $channel->basic_consume($queue, '', false, false, false, false, function ($msg) use ($callback, $queue) {
            $retryHeader = 'x-retry-count';
            $maxRetries = 3;

            $retryCount = isset($msg->get_properties()['application_headers']) ?
                $msg->get_properties()['application_headers']->getNativeData()[$retryHeader] ?? 0 : 0;

            try {
                $callback($msg);
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            } catch (Exception $e) {
                $retryCount++;
                if ($retryCount <= $maxRetries) {
                    $msg->set('application_headers', new AMQPTable([$retryHeader => $retryCount]));
                    $msg->delivery_info['channel']->basic_publish($msg, '', $queue);
                } else {
                    $this->logger->log($e, ILogger::ERROR);
                }
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            }
        });

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function close(): void
    {
        $this->amqProxy->close();
    }
}