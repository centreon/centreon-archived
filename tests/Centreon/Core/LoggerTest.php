<?php

use \Centreon\Core\Logger;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    private $config;

    public function setUp()
    {
        $filename = DATA_DIR . '/test-loggers.ini';
        $this->config = new \Centreon\Core\Config($filename);
        parent::setUp();
    }

    public function tearDown()
    {
        \Monolog\Registry::clear();
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
