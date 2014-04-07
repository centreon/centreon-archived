<?php
/**
* Smarty PHPunit tests deault template handler
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for block plugin tests
*/
class DefaultConfigHandlerTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->force_compile = true;
        $this->smarty->disableSecurity();
    }

    static function isRunnable()
    {
        return true;
    }

    public function testUnknownConfig()
    {
        try {
            $this->smarty->configLoad('foo.conf');
            $this->assertEquals("123.4", $this->smarty->fetch('eval:{#Number#}'));
        } catch (Exception $e) {
            $this->assertContains('Unable to read config file', $e->getMessage());

            return;
        }
        $this->fail('Exception for none existing config has not been raised.');
    }

    public function testRegisterNoneExistentHandlerFunction()
    {
        try {
            $this->smarty->registerDefaultConfigHandler('foo');
        } catch (Exception $e) {
            $this->assertContains("Default config handler 'foo' not callable", $e->getMessage());

            return;
        }
        $this->fail('Exception for non-callable function has not been raised.');
    }

    public function testDefaultConfigHandlerReplacement()
    {
        $this->smarty->registerDefaultConfigHandler('my_config_handler');
        $this->smarty->configLoad('foo.conf');
        $this->assertEquals("bar", $this->smarty->fetch('eval:{#foo#}'));
    }

    public function testDefaultConfigHandlerReplacementByConfigFile()
    {
        $this->smarty->registerDefaultConfigHandler('my_config_handler_file');
        $this->smarty->configLoad('foo.conf');
        $this->assertEquals("123.4", $this->smarty->fetch('eval:{#Number#}'));
    }

    public function testDefaultConfigHandlerReturningFalse()
    {
        $this->smarty->registerDefaultConfigHandler('my_config_false');
        try {
            $this->smarty->configLoad('foo.conf');
            $this->assertEquals("123.4", $this->smarty->fetch('eval:{#Number#}'));
        } catch (Exception $e) {
            $this->assertContains('Unable to read config file', $e->getMessage());

            return;
        }
        $this->fail('Exception for none existing template has not been raised.');
    }

    public function testConfigResourceDb4()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $this->smarty->configLoad('db4:foo.conf');
        $this->assertEquals("bar", $this->smarty->fetch('eval:{#foo#}'));
    }

}

function my_config_handler ($resource_type, $resource_name, &$config_source, &$config_timestamp, Smarty $smarty)
{
    $output = "foo = 'bar'\n";
    $config_source = $output;
    $config_timestamp = time();

    return true;
}
function my_config_handler_file ($resource_type, $resource_name, &$config_source, &$config_timestamp, Smarty $smarty)
{
    return $smarty->getConfigDir(0) . 'test.conf';
}
function my_config_false ($resource_type, $resource_name, &$config_source, &$config_timestamp, Smarty $smarty)
{
    return false;
}
