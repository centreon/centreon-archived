<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */


namespace Test\Centreon;

use Centreon\Internal\Config;
use Centreon\Internal\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    private $datadir;

    public function setUp()
    {
        $this->datadir = CENTREON_PATH . '/core/tests/data';
    }

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
        $config = new Config($this->datadir . '/test-nocache.ini');
        $cache = Cache::load($config);
        $this->assertInstanceOf('\Desarrolla2\Cache\Cache', $cache);
        $config = new Config($this->datadir . '/test-apc.ini');
        $cache = Cache::load($config);
        $this->assertInstanceOf('\Desarrolla2\Cache\Cache', $cache);
    }

    public function testApc()
    {
        if (false === extension_loaded('apc') || ini_get('apc.enable_cli') == 1) {
            $this->markTestIncomplete("APC extensions not found");
        }
        $config = new Config($this->datadir . '/test-apc.ini');
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
        $config = new Config($this->datadir . '/test-memcache.ini');
        $cache = Cache::load($config);
        $cache->set('app:test', array('test'));
        $this->assertEquals(array('test'), $cache->get('app:test'));
        $obj = new \StdClass();
        $cache->set('app:test2', $obj);
        $this->assertEquals($obj, $cache->get('app:test2'));
        $this->assertFalse($cache->has('app:noexists'));
    }
}
