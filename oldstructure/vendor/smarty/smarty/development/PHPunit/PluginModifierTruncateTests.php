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
class PluginModifierTruncateTests extends PHPUnit_Framework_TestCase
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
        $result = 'Two Sisters Reunite after Eighteen Years at Checkout Counter.';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testDefaultWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = 'Two Sisters Reunite after Eighteen Years at Checkout Counter.';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testLength()
    {
        $result = 'Two Sisters Reunite after...';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testLengthWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = 'Two Sisters Reunite after...';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testEtc()
    {
        $result = 'Two Sisters Reunite after';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30:""}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testEtcWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = 'Two Sisters Reunite after';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30:""}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testEtc2()
    {
        $result = 'Two Sisters Reunite after---';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30:"---"}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testEtc2WithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = 'Two Sisters Reunite after---';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30:"---"}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testBreak()
    {
        $result = 'Two Sisters Reunite after Eigh';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30:"":true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testBreakWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = 'Two Sisters Reunite after Eigh';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30:"":true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testBreak2()
    {
        $result = 'Two Sisters Reunite after E...';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30:"...":true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testBreak2WithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = 'Two Sisters Reunite after E...';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30:"...":true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testMiddle()
    {
        $result = 'Two Sisters Re..ckout Counter.';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30:"..":true:true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testMiddleWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = 'Two Sisters Re..ckout Counter.';
        $tpl = $this->smarty->createTemplate('eval:{"Two Sisters Reunite after Eighteen Years at Checkout Counter."|truncate:30:"..":true:true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

}
