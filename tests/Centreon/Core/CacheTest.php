<?php

namespace Test\Centreon\Centreon;

use \Centreon\Core\Config;
use \Centreon\Core\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        if (extension_loaded('apc')) {
            \apc_clear_cache();
        }
        if (extension_loaded('memcache')) {
            $memcache = new \Memcache();
            if ($memcache->addServer('localhost', 11211)) {
                $memcache->flush();
            }
        }
    }

    public function testCache()
    {
        $config = new Config(DATA_DIR . '/test-nocache.ini');
        $cache = Cache::load($config);
        $this->assertInstanceOf('\Desarrolla2\Cache\Cache', $cache);
        $config = new Config(DATA_DIR . '/test-apc.ini');
        $cache = Cache::load($config);
        $this->assertInstanceOf('\Desarrolla2\Cache\Cache', $cache);
    }

    public function testApc()
    {
        if (false === extension_loaded('apc') || ini_get('apc.enable_cli') == 0) {
            $this->markTestIncomplete("APC extensions not found");
        }
        $config = new Config(DATA_DIR . '/test-apc.ini');
        $cache = Cache::load($config);
        $cache->set('app:test', array());
        $this->assertEquals(array(), $cache->get('app:test'));
        $obj = new \StdClass();
        $cache->set('app:test2', $obj);
        $this->assertEquals($obj, $cache->get('app:test2'));
        $this->assertFalse($cache->has('app:noexists'));
    }

    public function testMemcache()
    {
        $this->markTestIncomplete("Memcache extensions not found");
        // @todo fix problem with memcache
        if (false === extension_loaded('memcache')) {
            $this->markTestIncomplete("Memcache extensions not found");
        }
        $config = new Config(DATA_DIR . '/test-memcache.ini');
        $cache = Cache::load($config);
        $cache->set('app:test', array('test'));
        $this->assertEquals(array('test'), $cache->get('app:test'));
        $obj = new \StdClass();
        $cache->set('app:test2', $obj);
        $this->assertEquals($obj, $cache->get('app:test2'));
        $this->assertFalse($cache->has('app:noexists'));
    }
}
