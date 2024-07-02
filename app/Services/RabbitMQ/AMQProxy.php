<?php declare(strict_types=1);

namespace App\Services\RabbitMQ;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use RuntimeException;

class AMQProxy
{
    /**
     * @var AMQPStreamConnection
     */
    private AMQPStreamConnection $connection;

    /**
     * @var AMQPChannel
     */
    private AMQPChannel $channel;

    /**
     * @param AMQPStreamConnection $connection
     */
    public function __construct(AMQPStreamConnection $connection)
    {
        $this->connection = $connection;
        $this->channel = $this->connection->channel();
    }

    /**
     * @return AMQPChannel
     * @throws Exception
     */
    public function getChannel(): AMQPChannel
    {
        if (!$this->connection->isConnected()) {
            $this->reconnect();
        }

        return $this->channel;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function reconnect(): void
    {
        $attempt = 0;
        while ($attempt <= 5) {
            try {
                $this->close();
                $this->connection->reconnect();
                $this->channel = $this->connection->channel();
                break;
            } catch (Exception $e) {
                $attempt++;
                sleep(min(2 ** $attempt, 60));
            }
        }

        if ($attempt > 5) {
            throw new RuntimeException("Failed to reconnect after $attempt attempts.");
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function close(): void
    {
        if ($this->channel->is_open()) {
            $this->channel->close();
        }

        if ($this->connection->isConnected()) {
            $this->connection->close();
        }
    }
}
