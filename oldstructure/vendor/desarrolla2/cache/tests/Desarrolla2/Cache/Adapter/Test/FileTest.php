<?php

/**
 * This file is part of the Cache project.
 *
 * Description of CacheTest
 *
 * @author : Daniel González Cerviño <daniel.gonzalez@freelancemadrid.es>
 */

namespace Desarrolla2\Cache\Adapter\Test;

use Desarrolla2\Cache\Adapter\Test\AbstractCacheTest;
use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\Adapter\File;

/**
 * Class FileTest
 *
 * @author Daniel González <daniel.gonzalez@freelancemadrid.es>
 */
class FileTest extends AbstractCacheTest
{

    public function setUp()
    {
        parent::setup();
        $this->cache = new Cache(new File($this->config['file']['dir']));
    }

    /**
     * @return array
     */
    public function dataProviderForOptionsException()
    {
        return array(
            array('ttl', 0, '\Desarrolla2\Cache\Exception\FileCacheException'),
            array('file', 100, '\Desarrolla2\Cache\Exception\FileCacheException'),
        );
    }
}
