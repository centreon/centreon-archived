<?php
/**
* Smarty PHPunit basic core function tests
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class core function tests
*/
class CoreTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * loadPlugin test unkown plugin
    */
    public function testLoadPluginErrorReturn()
    {
        $this->assertFalse($this->smarty->loadPlugin('Smarty_Not_Known'));
    }
    /**
    * loadPlugin test Smarty_Internal_Debug exists
    */
    public function testLoadPluginSmartyInternalDebug()
    {
        $this->assertTrue($this->smarty->loadPlugin('Smarty_Internal_Debug') == true);
    }
    /**
    * loadPlugin test $template_class exists
    */
    public function testLoadPluginSmartyTemplateClass()
    {
        $this->assertTrue($this->smarty->loadPlugin($this->smarty->template_class) == true);
    }
    /**
    * loadPlugin test loaging from plugins_dir
    */
    public function testLoadPluginSmartyPluginCounter()
    {
        $this->assertTrue($this->smarty->loadPlugin('Smarty_Function_Counter') == true);
    }
}
