<?php
/**
* Smarty PHPunit tests for templateExists methode
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for templateExists tests
*/
class TemplateExistsTests extends PHPUnit_Framework_TestCase
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
    * test $smarty->templateExists true
    */
    public function testSmartyTemplateExists()
    {
        $this->assertTrue($this->smarty->templateExists('helloworld.tpl'));
    }
    /**
    * test $smarty->templateExists false
    */
    public function testSmartyTemplateNotExists()
    {
        $this->assertFalse($this->smarty->templateExists('notthere.tpl'));
    }
}
