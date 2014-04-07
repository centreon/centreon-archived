<?php
/**
* Smarty PHPunit tests compilation of function plugins
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for function plugin tests
*/
class CompileFunctionPluginTests extends PHPUnit_Framework_TestCase
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
    * test function plugin tag in template file
    */
    public function testFunctionPluginFromTemplateFile()
    {
        $tpl = $this->smarty->createTemplate('functionplugintest.tpl', $this->smarty);
        $this->assertEquals("10", $this->smarty->fetch($tpl));
    }
    /**
    * test function plugin tag in compiled template file
    */
    public function testFunctionPluginFromCompiledTemplateFile()
    {
        $this->smarty->force_compile = false;
        $tpl = $this->smarty->createTemplate('functionplugintest.tpl', $this->smarty);
        $this->assertEquals("10", $this->smarty->fetch($tpl));
    }
    /**
    * test function plugin function definition in script
    */
    public function testFunctionPluginRegisteredFunction()
    {
        $this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION,'plugintest', 'myplugintest');
        $tpl = $this->smarty->createTemplate('eval:{plugintest foo=bar}', $this->smarty);
        $this->assertEquals("plugin test called bar", $this->smarty->fetch($tpl));
    }

    /**
    * test muiltiline tags
    */
    public function testMultiLineTags()
    {
        $this->assertEquals("10", $this->smarty->fetch("eval:{counter\n\tstart=10}"));
    }
}
        function myplugintest($params, &$smarty)
        {
            return "plugin test called $params[foo]";
        }
