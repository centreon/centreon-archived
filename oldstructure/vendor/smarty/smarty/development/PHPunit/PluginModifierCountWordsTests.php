<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

/**
* class for modifier tests
*/
class PluginModifierCountWordsTests extends PHPUnit_Framework_TestCase
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

    public function testDefault()
    {
        $result = "7";
        $tpl = $this->smarty->createTemplate('eval:{"Dealers Will Hear Car Talk at Noon."|count_words}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testDefaultWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = "7";
        $tpl = $this->smarty->createTemplate('eval:{"Dealers Will Hear Car Talk at Noon."|count_words}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testDashes()
    {
        $result = "7";
        $tpl = $this->smarty->createTemplate('eval:{"Smalltime-Dealers Will Hear Car Talk at Noon."|count_words}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testDashesWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = "7";
        $tpl = $this->smarty->createTemplate('eval:{"Smalltime-Dealers Will Hear Car Talk at Noon."|count_words}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testUmlauts()
    {
        $result = "7";
        $tpl = $this->smarty->createTemplate('eval:{"Dealers Will Hear Cär Talk at Nöön."|count_words}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testUmlautsWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = "7";
        $tpl = $this->smarty->createTemplate('eval:{"Dealers Will Hear Cär Talk at Nöön."|count_words}');
        $this->assertNotEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

}
