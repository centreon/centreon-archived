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

use Centreon\Internal\Db;

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
