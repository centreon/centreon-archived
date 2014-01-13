<?php

namespace Test\Centreon\Centreon;

require 'Centreon/Core/Config.php';
require 'Centreon/Core/Exception.php';

use \Centreon\Core\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testFile()
    {
        $filename = DATA_DIR . '/test-config.ini';
        $config = new Config($filename);
        $this->assertEquals('user1', $config->get('db_centreon', 'username'));
        $this->assertEquals(null, $config->get('db_centreon', 'novar'));
        $this->assertEquals('default', $config->get('nosection', 'novar', 'default'));
    }
}
