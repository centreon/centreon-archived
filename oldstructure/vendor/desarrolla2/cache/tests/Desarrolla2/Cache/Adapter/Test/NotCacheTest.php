<?php

/**
 * This file is part of the Cache project.
 *
 * Description of CacheTest
 *
 * @author : Daniel González Cerviño <daniel.gonzalez@freelancemadrid.es>
 */

namespace Desarrolla2\Cache\Adapter\Test;

use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\Adapter\NotCache;

/**
 * Class NotCacheTest
 *
 * @author Daniel González <daniel.gonzalez@freelancemadrid.es>
 */
class NotCacheTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Desarrolla2\Cache\Cache
     */
    protected $cache;

    public function setUp()
    {
        $this->cache = new Cache(new NotCache());
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return array(
            array(),
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHas()
    {
        $this->cache->set('key', 'value');
        $this->assertFalse($this->cache->has('key', 'value'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGet()
    {
        $this->cache->set('key', 'value');
        $this->assertFalse($this->cache->get('key'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSet()
    {
        $this->assertNull($this->cache->set('key', 'value'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testDelete()
    {
        $this->assertNull($this->cache->delete('key'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetOption()
    {
        $this->cache->setOption('ttl', 3600);
    }
}
