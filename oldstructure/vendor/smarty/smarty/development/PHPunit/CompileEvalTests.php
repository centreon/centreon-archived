<?php
/**
* Smarty PHPunit tests compilation of {eval} tag
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for {eval} tag tests
*/
class CompileEvalTests extends PHPUnit_Framework_TestCase
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
    * test eval tag
    */
    public function testEval1()
    {
        $tpl = $this->smarty->createTemplate("eval:{eval var='hello world'}");
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    public function testEval2()
    {
        $tpl = $this->smarty->createTemplate("eval:{eval var='hello world' assign=foo}{\$foo}");
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    public function testEval3()
    {
        $tpl = $this->smarty->createTemplate("eval:{eval var='hello world' assign=foo}");
        $this->assertEquals("", $this->smarty->fetch($tpl));
    }
}
