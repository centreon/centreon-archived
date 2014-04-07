<?php
/**
 * This file is part of the Cache project.
 *
 * Copyright (c)
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Desarrolla2\Cache\Adapter\Test;

use Desarrolla2\Cache\Adapter\Test\AbstractCacheTest;
use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\Adapter\Redis;

/**
 * Class RedisTest
 *
 * @author Daniel González <daniel.gonzalez@freelancemadrid.es>
 */

class RedisTest extends AbstractCacheTest
{

    public function setUp()
    {
        parent::setup();
        $this->cache = new Cache(
            new Redis()
        );
    }

    /**
     * @return array
     */
    public function dataProviderForOptions()
    {
        return array(
            array('ttl', 100),
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
}
