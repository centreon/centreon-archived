<?php

/**
 * This file is part of the Cache project.
 *
 */

namespace Desarrolla2\Cache\Test;

use Desarrolla2\Cache\Cache;

/**
 * Class CacheTest
 *
 * @author Daniel González <daniel.gonzalez@freelancemadrid.es>
 */
class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Desarrolla2\Cache\Cache
     */
    protected $cache;

    public function setUp()
    {
        $this->cache = new Cache();
    }

    /**
     * @expectedException \Desarrolla2\Cache\Exception\AdapterNotSetException
     */
    public function testGetAdapterThrowsException()
    {
        $this->cache->getAdapter();
    }
}
