<?php

namespace Test\Centreon\Centreon;

use \Centreon\Core\Config;
use \Centreon\Core\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function testCache()
    {
        $config = new Config(DATA_DIR . '/test-nocache.ini');
        $cache = Cache::load($config);
        $this->assertInstanceOf('\Desarrolla2\Cache\Cache', $cache);
        $config = new Config(DATA_DIR . '/test-apc.ini');
        $cache = Cache::load($config);
        $this->assertInstanceOf('\Desarrolla2\Cache\Cache', $cache);
    }
}
