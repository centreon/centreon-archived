<?php

namespace CentreonRemote\Tests\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use CentreonRemote\Infrastructure\Service\ExporterCacheService;

/**
 * @group CentreonRemote
 */
class ExporterCacheServiceTest extends TestCase
{

    protected function setUp(): void
    {
        $this->cache = new ExporterCacheService();
        $this->cache->set('key1', 'val1');
        $this->cache->set('key2', 'val2');
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExporterCacheService::getIf
     */
    public function testGetIf()
    {
        $callable = function () {
            return 'val1a';
        };

        $result = $this->cache->getIf('key1', $callable);

        $this->assertEquals('val1', $result);

        $result = $this->cache->getIf('key1a', $callable);

        $this->assertEquals('val1a', $result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExporterCacheService::get
     */
    public function testGet()
    {
        $result = $this->cache->get('key1');

        $this->assertEquals('val1', $result);

        $result = $this->cache->get('key1a');

        $this->assertNull($result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExporterCacheService::has
     */
    public function testHas()
    {
        $result = $this->cache->has('key1');

        $this->assertTrue($result);

        $result = $this->cache->has('key1a');

        $this->assertFalse($result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExporterCacheService::merge
     */
    public function testMerge()
    {
        $this->cache->set('key3', ['val3']);
        $this->cache->merge('key3', ['val3a']);

        $this->assertEquals(['val3a', 'val3'], $this->cache->get('key3'));

        $this->cache->merge('key3aa', ['val3aa']);

        $this->assertEquals(['val3aa'], $this->cache->get('key3aa'));
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExporterCacheService::set
     */
    public function testSet()
    {
        $this->cache->set('key4', 'val4');

        $this->assertTrue($this->cache->has('key4'));
        $this->assertEquals('val4', $this->cache->get('key4'));

        $this->cache->set('key4', 'val4a');

        $this->assertEquals('val4a', $this->cache->get('key4'));
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExporterCacheService::destroy
     */
    public function testDestroy()
    {
        $this->assertTrue($this->cache->has('key1'));

        $this->cache->destroy();

        $this->assertFalse($this->cache->has('key1'));
    }
}
