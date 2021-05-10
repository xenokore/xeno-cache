<?php

namespace Xenokore\Cache;

use Xenokore\Utility\Helper\StringHelper;

use Predis\CommunicationException;

class EventCache implements EventCacheInterface
{
    private $client;

    public function __construct(PredisClientFactory $factory)
    {
        $this->client = $factory->createPredisPubSubClient();
    }

    public function publish(string $channel, string $data)
    {
        if (!$this->checkConnection()) {
            return false;
        }

        return $this->client->publish($channel, $data);
    }

    /**
     * Subscribe a callback to a channel. This method operates as an infinite synchronous loop.
     *
     * @param string   $channel     The channel to subscribe to
     * @param callable $callback    The callback function. ex:
     *                              function(\Predis\PubSub\Consumer $pubsub, object $message){}
     * @return mixed                false|\Predis\PubSub\Consumer
     */
    public function subscribe(string $channel, callable $callback)
    {
        if (!$this->checkConnection()) {
            return false;
        }

        $pubsub = $this->client->pubSubLoop();

        $pubsub->subscribe($channel);

        try {
            foreach ($pubsub as $message) {
                // Allows the callback to return false to end the loop
                if (call_user_func($callback, $pubsub, $message) === false) {
                    $pubsub->stop();
                }
            }
        } catch (CommunicationException $ex) {
            // Restart the pubsub consumer on line errors
            // (`CommunicationException` does not throw a useable code)
            if (StringHelper::startsWith($ex->getMessage(), 'Error while reading line from the server.')) {
                return $this->subscribe($channel, $callback);
            }
        }

        return $pubsub;
    }

    private function checkConnection(): bool
    {
        if (!$this->client) {
            return false;
        }

        $conn = $this->client->getConnection();

        if (!$conn) {
            return false;
        }

        if (!$conn->isConnected()) {
            try {
                $conn->connect();
            } catch (\Exception $ex) {
            }
        }

        return $conn->isConnected();
    }
}
