<?php

namespace Xenokore\Cache\Tests;

use Xenokore\Cache\EventCache;
use Xenokore\Cache\PredisClientFactory;

use Xenokore\Utility\Helper\StringHelper;
use Xenokore\Utility\Helper\ClassHelper;

use PHPUnit\Framework\TestCase;

/**
 * Borrowed some tests from:
 * - https://github.com/php-cache/integration-tests
 */
class EventCacheTest extends TestCase
{
    private $event_cache;

    protected function setUp(): void
    {
        $event_cache = new EventCache(
            new PredisClientFactory()
        );

        $this->assertInstanceOf(EventCache::class, $event_cache);

        if (!ClassHelper::callPrivateMethod($event_cache, 'checkConnection')) {
            $this->markTestSkipped(
                'Couldn\'t connect to Redis server. Skipping `EventCache` tests'
            );
        }

        $this->event_cache = $event_cache;
    }

    public function testPublish()
    {
        $this->assertIsInt($this->event_cache->publish('test', 'test_string'));
    }

    public function testSubscribe()
    {
        // subscribe is not async without an extension so the script would just loop endlessly
        // we'll skip it until an async solution is implemented
        // TODO: implement async pubsub test
        $this->markTestSkipped(
            'Skipping `Predis Subscribe` test'
        );
    }
}
