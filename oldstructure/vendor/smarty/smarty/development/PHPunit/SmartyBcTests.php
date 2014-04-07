<?php
/**
* Smarty PHPunit tests SmartyBC code
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class SmartyBC class tests
*/
class SmartyBcTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SmartyTests::init();
        $this->smartyBC = SmartyTests::$smartyBC;
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * test {php} tag
    */
    public function testSmartyPhpTag()
    {
        $this->assertEquals('hello world', $this->smartyBC->fetch('eval:{php} echo "hello world"; {/php}'));
    }

}
