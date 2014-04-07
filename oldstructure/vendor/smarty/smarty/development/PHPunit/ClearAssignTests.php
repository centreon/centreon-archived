<?php
/**
* Smarty PHPunit tests clearing assigned variables
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for clearing assigned variables tests
*/
class ClearAssignTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        $this->smartyBC = SmartyTests::$smartyBC;
        SmartyTests::init();

        $this->smarty->assign('foo','foo');
        $this->smarty->assign('bar','bar');
        $this->smarty->assign('blar','blar');

        $this->smartyBC->assign('foo','foo');
        $this->smartyBC->assign('bar','bar');
        $this->smartyBC->assign('blar','blar');
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * test all variables accessable
    */
    public function testAllVariablesAccessable()
    {
        $this->assertEquals('foobarblar', $this->smarty->fetch('eval:{$foo}{$bar}{$blar}'));
    }

    /**
    * test simple clear assign
    */
    public function testClearAssign()
    {
         $this->smarty->error_reporting  = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
        $this->smarty->clearAssign('blar');
        $this->assertEquals('foobar', $this->smarty->fetch('eval:{$foo}{$bar}{$blar}'));
    }
    public function testSmarty2ClearAssign()
    {
         $this->smartyBC->error_reporting  = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
        $this->smartyBC->clear_assign('blar');
        $this->assertEquals('foobar', $this->smartyBC->fetch('eval:{$foo}{$bar}{$blar}'));
    }
    /**
    * test clear assign array of variables
    */
    public function testArrayClearAssign()
    {
         $this->smarty->error_reporting  = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
        $this->smarty->clearAssign(array('blar','foo'));
        $this->assertEquals('bar', $this->smarty->fetch('eval:{$foo}{$bar}{$blar}'));
    }
    public function testSmarty2ArrayClearAssign()
    {
         $this->smartyBC->error_reporting  = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
        $this->smartyBC->clear_assign(array('blar','foo'));
        $this->assertEquals('bar', $this->smartyBC->fetch('eval:{$foo}{$bar}{$blar}'));
    }
}
