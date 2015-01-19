<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
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
