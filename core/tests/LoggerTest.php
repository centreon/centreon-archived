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

use Centreon\Internal\Logger;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    private $config;
    private $datadir;

    public static function setUpBeforeClass()
    {
        \Monolog\Registry::clear();
    }

    public function setUp()
    {
        $this->datadir = CENTREON_PATH . '/core/tests/data/';
        $filename = $this->datadir . '/test-loggers.ini';
        $this->config = new \Centreon\Internal\Config($filename);
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testLoad()
    {
        Logger::load($this->config);
        $logger = \Monolog\Registry::getInstance('MAIN');
        $this->assertInstanceOf('\Monolog\Logger', $logger);
        $handler = $logger->popHandler();
        $this->assertInstanceOf('\Monolog\Handler\StreamHandler', $handler);
    }
}
