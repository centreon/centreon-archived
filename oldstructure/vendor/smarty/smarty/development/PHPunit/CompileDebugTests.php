<?php
/**
* Smarty PHPunit tests compilation of {debug} tag
*
* @package PHPunit
* @author Uwe Tews
*/

require_once SMARTY_DIR . 'Smarty.class.php';

/**
* class for {debug} tag tests
*/
class CompileDebugTests extends PHPUnit_Framework_TestCase
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
    * test debug tag
    */
    public function testDebugTag()
    {
        $tpl = $this->smarty->createTemplate("eval:{debug}");
        $_contents = $this->smarty->fetch($tpl);
        $this->assertContains("Smarty Debug Console", $_contents);
    }
    /**
    * test debug property
    */
    public function testDebugProperty()
    {
        $this->smarty->debugging = true;
        $tpl = $this->smarty->createTemplate("eval:hello world");
        ob_start();
        $this->smarty->display($tpl);
        $_contents = ob_get_clean();
        $this->assertContains("Smarty Debug Console", $_contents);
    }
}
