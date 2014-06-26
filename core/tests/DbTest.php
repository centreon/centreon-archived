<?php

namespace Test\Centreon;

use \Centreon\Internal\Db;

class DbTest extends \PHPUnit_Framework_TestCase
{
    public static $dsn = 'sqlite::memory:';

    public function testConnection()
    {
        $dbconn = new Db(self::$dsn);
        $this->assertInstanceOf('\Centreon\Internal\Db', $dbconn);
    }

    public function testStatement()
    {
        $dbconn = new Db(self::$dsn);
        $stmt = $dbconn->query('SELECT 1');
        $this->assertInstanceOf('\Centreon\Internal\Db\Statement', $stmt);
        $stmt = $dbconn->prepare('SELECT 1');
        $this->assertInstanceOf('\Centreon\Internal\Db\Statement', $stmt);
    }

    public function testFetchRow()
    {
        $dbconn = new Db(self::$dsn);
        $dbconn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $stmt = $dbconn->query('SELECT 1');
        $row = $stmt->fetchRow();
        $this->assertEquals(array(1 => '1'), $row);
    }
}
