<?php
/**
* Smarty PHPunit tests of constants
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for constants tests
*/
class ConstantsTests extends PHPUnit_Framework_TestCase
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
    * test constants
    */
    public function testConstants()
    {
        define('MYCONSTANTS','hello world');
        $tpl = $this->smarty->createTemplate('eval:{$smarty.const.MYCONSTANTS}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
/**
    public function testConstants2()
    {
        $tpl = $this->smarty->createTemplate('eval:{MYCONSTANTS}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    public function testConstants3()
    {
        $tpl = $this->smarty->createTemplate('eval:{$x=MYCONSTANTS}{$x}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
*/
}
