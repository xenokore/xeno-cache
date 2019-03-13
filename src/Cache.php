<?php

namespace Xenokore\Cache;

use SubjectivePHP\Psr\SimpleCache\RedisCache;

use Predis\Connection\ConnectionException;

/**
 * Super Lazy PSR-16 cache service
 *
 * Borrowed some stuff from:
 * - https://github.com/subjective-php/psr-cache-redis/
 *
 * TODO: this class could use CacheAwareTrait
 */
class Cache implements CacheInterface
{
    private $predis_client;

    private $simple_cache_client;

    public function __construct(PredisClientFactory $factory)
    {
        $this->predis_client = $factory->createPredisClient();
    }

    private function _getSimpleCacheClient()
    {
        // Return SimpleCache implementation if it already exists
        if (isset($this->simple_cache_client)) {
            return $this->simple_cache_client;
        }

        // A PredisClient must exist
        if (!$this->predis_client) {
            return false;
        }

        // Check if it's possible to get the connection object
        $conn = $this->predis_client->getConnection();
        if (!$conn) {
            return false;
        }

        // Try to connect if we aren't
        if (!$conn->isConnected()) {
            try {
                $conn->connect();
            } catch (ConnectionException  $ex) {
            }
        }

        // Make sure we're connected
        if (!$conn->isConnected()) {
            return false;
        }

        // Create SimpleCache implementation
        $simple_cache_client = new RedisCache($this->predis_client);
        if ($simple_cache_client) {
            return $this->simple_cache_client = $simple_cache_client;
        }

        // Failed to create SimpleCache implementation
        return $this->simple_cache_client = false;
    }

    private function _executeCommand(string $command, $default, array ...$arguments)
    {
        $cache = $this->_getSimpleCacheClient();
        if (!$cache) {
            return $default;
        }
        if (count($arguments) > 0) {
            return $cache->$command(...$arguments);
        } else {
            return $cache->$command();
        }
    }

    public function get($key, $default = null)
    {
        return $this->_executeCommand(__FUNCTION__, null, func_get_args());
    }

    public function set($key, $value, $ttl = null)
    {
        return $this->_executeCommand(__FUNCTION__, false, func_get_args());
    }

    public function delete($key)
    {
        return $this->_executeCommand(__FUNCTION__, false, func_get_args());
    }

    public function clear()
    {
        return $this->_executeCommand(__FUNCTION__, false);
    }

    public function getMultiple($keys, $default = null)
    {
        $result = $this->_executeCommand(__FUNCTION__, null, func_get_args());
        if (is_array($result)) {
            return $result;
        }

        $return_array = [];

        if (is_null($result)) {
            foreach ($keys as $key) {
                $return_array[$key] = $default;
            }
        }

        return [];
    }

    public function setMultiple($values, $ttl = null)
    {
        return $this->_executeCommand(__FUNCTION__, false, func_get_args());
    }

    public function deleteMultiple($keys)
    {
        return $this->_executeCommand(__FUNCTION__, false, func_get_args());
    }

    public function has($key)
    {
        return $this->_executeCommand(__FUNCTION__, false, func_get_args());
    }
}
