<?php

namespace Test\Centreon;

use \Centreon\Internal\Logger;

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
