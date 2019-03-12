<?php

namespace Xenokore\Cache;

interface EventCacheInterface
{
    // Redis pub/sub
    public function publish(string $channel, string $data);
    public function subscribe(string $channel, callable $callbac);
}
