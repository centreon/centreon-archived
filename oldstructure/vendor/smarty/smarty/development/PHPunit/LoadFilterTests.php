<?php
/**
* Smarty PHPunit tests loadFilter method
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for loadFilter method tests
*/
class LoadFilterTests extends PHPUnit_Framework_TestCase
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
    * test loadFilter method
    */
    public function testLoadFilter()
    {
        $this->smarty->loadFilter('output', 'trimwhitespace');
        $this->assertTrue(is_callable($this->smarty->registered_filters['output']['smarty_outputfilter_trimwhitespace']));
    }
}
