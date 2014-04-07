<?php
/**
* Smarty PHPunit tests for escape_html property
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for escape_html property tests
*/
class AutoEscapeTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->escape_html = true;
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * test escape_html property
    */
    public function testAutoEscape()
    {
        $tpl = $this->smarty->createTemplate('eval:{$foo}');
        $tpl->assign('foo','<a@b.c>');
        $this->assertEquals("&lt;a@b.c&gt;", $this->smarty->fetch($tpl));
    }
}
