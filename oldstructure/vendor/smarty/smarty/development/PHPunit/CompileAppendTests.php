<?php
/**
* Smarty PHPunit tests compilation of append tags
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for append tags tests
*/
class CompileAppendTests extends PHPUnit_Framework_TestCase
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
    * test aappand tag
    */
    public function testAppend1()
    {
        $this->assertEquals("12", $this->smarty->fetch('eval:{$foo=1}{append var=foo value=2}{foreach $foo as $bar}{$bar}{/foreach}'));
    }
    public function testAppend2()
    {
        $this->smarty->assign('foo',1);
        $this->assertEquals("12", $this->smarty->fetch('eval:{append var=foo value=2}{foreach $foo as $bar}{$bar}{/foreach}'));
    }
}
