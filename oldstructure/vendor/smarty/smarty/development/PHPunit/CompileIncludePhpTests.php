<?php
/**
* Smarty PHPunit tests compilation of the {include_php} tag
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for {include_php} tests
*/
class CompileIncludePhpTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smartyBC = SmartyTests::$smartyBC;
        SmartyTests::init();
        $this->smartyBC->force_compile = true;
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * test include_php string file_name function
    */
    public function testIncludePhpStringFileName()
    {
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:start {include_php file='scripts/test_include_php.php'} end");
        $result= $this->smartyBC->fetch($tpl);
        $this->assertContains("test include php", $result);
    }
    /**
    * test include_php string file_name function
    */
    public function testIncludePhpVariableFileName()
    {
        $this->smartyBC->disableSecurity();
         $tpl = $this->smartyBC->createTemplate('eval:start {include_php file=$filename once=false} end');
        $tpl->assign('filename','scripts/test_include_php.php');
        $result= $this->smartyBC->fetch($tpl);
        $this->assertContains("test include php", $result);
    }
    public function testIncludePhpVariableFileNameShortag()
    {
        $this->smartyBC->disableSecurity();
         $tpl = $this->smartyBC->createTemplate('eval:start {include_php $filename once=false} end');
        $tpl->assign('filename','scripts/test_include_php.php');
        $result= $this->smartyBC->fetch($tpl);
        $this->assertContains("test include php", $result);
    }
}
