<?php

namespace Test\Centreon;

use \Centreon\Internal\Config,
    \Centreon\Internal\Cache,
    \Centreon\Internal\Di;

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
            $dbconn->exec("CREATE TABLE IF NOT EXISTS `options` (
                `group` VARCHAR(255) NOT NULL DEFAULT 'default',
                `key` VARCHAR(255) NULL,
                `value` VARCHAR(255) NULL
            )");
            $di = new \Centreon\Internal\Di();
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
        $datasetDb = $this->getConnection()->createQueryTable('options', 'SELECT * FROM options');
        $datasetTest = $this->createFlatXmlDataSet($this->datadir . '/test-config-set.xml')->getTable('options');
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
        $this->setExpectedException('\Centreon\Internal\Exception', "This configuration default - novariable does not exists into database."); 
        $config->set('default', 'novariable', 'test');
    }
}
