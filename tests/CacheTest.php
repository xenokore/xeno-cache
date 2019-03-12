<?php

use Xenokore\Cache\Cache;
use Xenokore\Cache\PredisClientFactory;

use Xenokore\Utility\Helper\StringHelper;
use Xenokore\Utility\Helper\ClassHelper;

use PHPUnit\Framework\TestCase;

/**
 * Borrowed some tests from:
 * - https://github.com/php-cache/integration-tests
 */
class CacheTest extends TestCase
{
    private $cache;

    protected function setUp(): void
    {
        $cache = new Cache(
            new PredisClientFactory()
        );

        $this->assertInstanceOf(Cache::class, $cache);

        $cache = ClassHelper::callPrivateMethod($cache, '_getSimpleCacheClient');

        if(!$cache){
            $this->markTestSkipped(
                'Couldn\'t connect to Redis server. Skipping `Cache` tests'
            );
        }

        $this->cache = $cache;
    }

    // protected function tearDown(): void
    // {
    //     if ($this->cache) {
    //         $this->cache->clear();
    //     }
    // }

    public function testNonFoundGet()
    {
        // When the `default` parameter is not given, an invalid key must return null
        $this->assertNull($this->cache->get(
            sha1(rand() . time())
        ));

        // When the `default` parameter is given, an invalid key must return the `default` variable back
        $this->assertEquals('abc', $this->cache->get(
            sha1(rand() . time()),
            'abc'
        ));
    }

    public function testDefault()
    {
        $key   = '__' . StringHelper::generate(12);
        $value = '__' . StringHelper::generate(12);

        // Must not be set yet
        $result = $this->cache->get($key);
        $this->assertNull($result, 'test value already set...');

        // Test the set() function
        $result = $this->cache->set($key, $value);
        $this->assertTrue($result, 'set() must return true if success');

        // Must now be set
        $this->assertEquals($value, $this->cache->get($key));
        
        // Delete
        $result = $this->cache->delete($key);
        $this->assertTrue($result, 'delete() must return true if success');

        // Must not be in cache anymore
        $result = $this->cache->get($key);
        $this->assertNull($result, 'cache item must be removed after delete()');
    }
}