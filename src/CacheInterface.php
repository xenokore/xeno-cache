<?php

namespace Xenokore\Cache;

interface CacheInterface extends \Psr\SimpleCache\CacheInterface
{
    // Original PSR-16 methods
    public function get($key, $default = null);
    public function set($key, $data, $ttl = null);
    public function has($key);
    public function delete($key);
    public function clear();
    public function getMultiple($keys, $default = null);
    public function setMultiple($values, $ttl = null);
    public function deleteMultiple($keys);
}
