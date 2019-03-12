<?php

namespace Xenokore\Cache;

use Xenokore\Utility\Helper\ArrayHelper;

use Predis\Client as PredisClient;
use Predis\Connection\ConnectionException;

class PredisClientFactory implements PredisClientFactoryInterface
{
    private $nodes          = [];
    private $client_options = [];
    private $enabled        = false;

    private $predis_client;
    private $predis_pubsub_client;

    public function __construct(?array $config_options = null)
    {
        $config = require __DIR__ . '/../config/cache.conf.default.php';

        // Merge default and given configuration
        if(!is_null($config_options)){
            $config = ArrayHelper::mergeRecursiveDistinct($config, $config_options);
        }

        // Get configuration
        $this->nodes          = (array) ($config['nodes'] ?? []);
        $this->client_options = (array) ($config['options'] ?? []);
        $this->enabled        = (bool) ($config['enabled'] ?? false);
    }

    public function createPredisClient(): ? PredisClient
    {
        try {

            if (!$this->enabled) {
                return null;
            }
    
            if (count($this->nodes) === 0) {
                return null;
            }
    
            if (count($this->client_options) > 0) {
                return new PredisClient($this->nodes, $this->client_options);
            }
            
            return new PredisClient($this->nodes);

        } catch (ConnectionException $ex){

            return null;
        }
    }

    public function createPredisPubSubClient(): ? PredisClient
    {
        try {

            if (!$this->enabled) {
                return null;
            }

            if (count($this->nodes) === 0) {
                return null;
            }

            $pubsub_options = ArrayHelper::mergeRecursiveDistinct(
                (array) $this->client_options,
                ['read_write_timeout' => 0]
            );

            return new PredisClient($this->nodes, $pubsub_options);

        } catch (ConnectionException $ex){

            return null;
        }
    }
}
