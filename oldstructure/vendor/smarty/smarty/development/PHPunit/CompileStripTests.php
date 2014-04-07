<?php
/**
* Smarty PHPunit tests compilation of strip tags
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for strip tags tests
*/
class CompileStripTests extends PHPUnit_Framework_TestCase
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
    * test strip tag
    */
    public function testStrip()
    {
        $tpl = $this->smarty->createTemplate("eval:{strip}<table>\n </table>{/strip}");
        $this->assertEquals('<table></table>', $this->smarty->fetch($tpl));
    }
}
