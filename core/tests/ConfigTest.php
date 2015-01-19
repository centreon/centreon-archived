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
use Centreon\Internal\Di;

class ConfigTest extends \PHPUnit_Extensions_Database_TestCase
{
    private $conn = null;
    private $datadir = null;

    public function setUp()
    {
        $this->datadir = CENTREON_PATH . '/core/tests/data';
        new Di();
        parent::setUp();
    }

    public function tearDown()
    {
        Di::reset();
        parent::tearDown();
    }

    public function getConnection()
    {
        if (is_null($this->conn)) {
            $dbconn = new \Centreon\Internal\Db('sqlite::memory:');
            $dbconn->exec(
                "CREATE TABLE IF NOT EXISTS `cfg_options` (
                `group` VARCHAR(255) NOT NULL DEFAULT 'default',
                `key` VARCHAR(255) NULL,
                `value` VARCHAR(255) NULL
                )"
            );
            \Centreon\Internal\Di::getDefault()->setShared('db_centreon', $dbconn);
            $this->conn = $this->createDefaultDBConnection($dbconn, ':memory:');
        }
        return $this->conn;
    }

    public function getDataSet()
    {
        return $this->createFlatXMLDataSet($this->datadir . '/test-config.xml');
    }

    public function testFileGet()
    {
        $filename = $this->datadir . '/test-config.ini';
        $config = new Config($filename);
        $this->assertEquals('user1', $config->get('db_centreon', 'username'));
        $this->assertEquals(null, $config->get('db_centreon', 'novar'));
        $this->assertEquals('default', $config->get('nosection', 'novar', 'default'));
    }

    public function testGetGroup()
    {
        $filename = $this->datadir . '/test-config.ini';
        $config = new Config($filename);
        $values = array(
            'dsn' => 'sqlite::memory:',
            'username' => 'user1'
        );
        $this->assertEquals($values, $config->getGroup('db_centreon'));
        $this->assertEquals(array(), $config->getGroup('no_section'));
    }

    public function testDbGet()
    {
        $filename = $this->datadir . '/test-config.ini';
        $config = new Config($filename);
        Di::getDefault()->setShared('cache', Cache::load($config));
        $config->loadFromDb();
        $this->assertEquals('value1', $config->get('default', 'variable1'));
        $this->assertEquals('value2', $config->get('default', 'variable2'));
    }

    public function testDbSet()
    {
        $filename = $this->datadir . '/test-config.ini';
        $config = new Config($filename);
        Di::getDefault()->setShared('cache', Cache::load($config));
        $config->loadFromDb();
        $config->set('default', 'variable2', 'test');
        $this->assertEquals('test', $config->get('default', 'variable2'));
        $datasetDb = $this->getConnection()->createQueryTable('cfg_options', 'SELECT * FROM cfg_options');
        $datasetTest = $this->createFlatXmlDataSet($this->datadir . '/test-config-set.xml')->getTable('cfg_options');
        $this->assertTablesEqual($datasetTest, $datasetDb);
    }

    public function testConstructFileNotExists()
    {
        $this->setExpectedException('\Centreon\Internal\Exception', "The configuration file is not readable.");
        new Config('nofile.ini');
    }

    public function testConstructFileHasErrors()
    {
        $this->setExpectedException('\Centreon\Internal\Exception', "Error when parsing configuration file.");
        new Config($this->datadir . '/test-config-errors.ini');
    }

    public function testSetExceptionBadGroup()
    {
        $filename = $this->datadir . '/test-config.ini';
        $config = new Config($filename);
        Di::getDefault()->setShared('cache', Cache::load($config));
        $config->loadFromDb();
        $this->setExpectedException('\Centreon\Internal\Exception', "This configuration group is not permit.");
        $config->set('cache', 'test', 'test');
    }

    public function testSetExceptionBadVariable()
    {
        $filename = $this->datadir . '/test-config.ini';
        $config = new Config($filename);
        Di::getDefault()->setShared('cache', Cache::load($config));
        $config->loadFromDb();
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "This configuration default - novariable does not exists into database."
        );
        $config->set('default', 'novariable', 'test');
    }
}
