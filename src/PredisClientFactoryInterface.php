<?php

namespace Xenokore\Cache;

use \Predis\Client as PredisClient;

interface PredisClientFactoryInterface
{
    public function createPredisClient(): ? PredisClient;

    public function createPredisPubSubClient(): ? PredisClient;
}
