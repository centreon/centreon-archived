<?php
/**
* Smarty PHPunit tests compilation of delimiter tags
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for delimiter tags tests
*/
class CompileDelimiterTests extends PHPUnit_Framework_TestCase
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
    * test delimiter tag test
    */
    public function testLeftDelimiter()
    {
        $tpl = $this->smarty->createTemplate('eval:x{ldelim}x');
        $this->assertEquals('x{x', $this->smarty->fetch($tpl));
    }
    public function testRightDelimiter()
    {
        $tpl = $this->smarty->createTemplate('eval:x{rdelim}x');
        $this->assertEquals('x}x', $this->smarty->fetch($tpl));
    }
}
