<?php

/**
 * This file is part of the Cache project.
 *
 * Copyright (c)
 * Daniel González <daniel.gonzalez@freelancemadrid.es>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Desarrolla2\Cache\Adapter\Test;

use Desarrolla2\Cache\Adapter\Test\AbstractCacheTest;
use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\Adapter\Memory;

/**
 *
 * Description of MemoryTest
 *
 * @author : Daniel González <daniel.gonzalez@freelancemadrid.es>
 */
class MemoryTest extends AbstractCacheTest
{
    public function setUp()
    {
        $this->cache = new Cache(new Memory());
    }

    /**
     * @return array
     */
    public function dataProviderForOptions()
    {
        return array(
            array('ttl', 100),
            array('limit', 100),
        );
    }

    /**
     * @return array
     */
    public function dataProviderForOptionsException()
    {
        return array(
            array('ttl', 0, '\Desarrolla2\Cache\Exception\CacheException'),
            array('file', 100, '\Desarrolla2\Cache\Exception\CacheException'),
        );
    }

    public function testExceededLimit()
    {
        $limit = 1;
        $this->cache->setOption('limit', $limit);
        for ($i = 0; $i <= $limit; $i++) {
            $this->cache->set($i, $i);
        }
        $this->assertFalse($this->cache->has($i));
    }
}
