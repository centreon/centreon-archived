<?php
/**
* Smarty PHPunit tests compilation of {function} tag
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for {function} tag tests
*/
class CompileFunctionTests extends PHPUnit_Framework_TestCase
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
    * test simple function call tag
    */
    public function testSimpleFunction()
    {
        $tpl = $this->smarty->createTemplate('test_template_function_tag1.tpl');
        $this->assertEquals("default param", $this->smarty->fetch($tpl));
    }
    /**
    * test simple function call tag 2
    */
    public function testSimpleFunction2()
    {
        $tpl = $this->smarty->createTemplate('test_template_function_tag2.tpl');
        $this->assertEquals("default param default param2", $this->smarty->fetch($tpl));
    }
    /**
    * test overwrite default function call tag
    */
    public function testOverwriteDefaultFunction()
    {
        $tpl = $this->smarty->createTemplate('test_template_function_tag3.tpl');
        $this->assertEquals("overwrite param default param2", $this->smarty->fetch($tpl));
    }
    /**
    * test recursive function call tag
    */
    public function testRecursiveFunction()
    {
        $tpl = $this->smarty->createTemplate('test_template_function_tag4.tpl');
        $this->assertEquals("012345", $this->smarty->fetch($tpl));
    }
    /**
    * test inherited function call tag
    */
    public function testInheritedFunction()
    {
        $tpl = $this->smarty->createTemplate('test_template_function_tag5.tpl');
        $this->assertEquals("012345", $this->smarty->fetch($tpl));
    }
    /**
    * test function definition in include
    */
    public function testDefineFunctionInclude()
    {
        $tpl = $this->smarty->createTemplate('test_template_function_tag6.tpl');
        $this->assertEquals("012345", $this->smarty->fetch($tpl));
    }
    /**
    * test external function definition
    */
    public function testExternalDefinedFunction()
    {
        $tpl = $this->smarty->createTemplate('eval:{include file=\'template_function_lib.tpl\'}{call name=template_func1}');
        $tpl->assign('foo', 'foo');
        $this->assertContains('foo foo', $this->smarty->fetch($tpl));
    }
    /**
    * test external function definition cached
    */
    public function testExternalDefinedFunctionCached1()
    {
        $this->smarty->caching = 1;
        $this->smarty->cache_lifetime = 1000;
        $this->smarty->clearAllCache();
        $tpl = $this->smarty->createTemplate('test_template_function.tpl');
        $tpl->assign('foo', 'foo');
        $this->assertContains('foo foo', $this->smarty->fetch($tpl));
    }
    /**
    * test external function definition cached 2
    */
    public function testExternalDefinedFunctionCached2()
    {
        $this->smarty->caching = 1;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('test_template_function.tpl');
        $this->assertTrue($this->smarty->isCached($tpl));
        $tpl->assign('foo', 'bar');
        $this->assertContains('foo bar', $this->smarty->fetch($tpl));
    }
    /**
    * test external function definition nocache call
    */
    public function testExternalDefinedFunctionNocachedCall1()
    {
        $this->smarty->caching = 1;
        $this->smarty->cache_lifetime = 1000;
        $this->smarty->clearAllCache();
        $tpl = $this->smarty->createTemplate('test_template_function_nocache_call.tpl');
        $tpl->assign('foo', 'foo');
        $this->assertContains('foo foo', $this->smarty->fetch($tpl));
    }
    /**
    * test external function definition nocache call 2
    */
    public function testExternalDefinedFunctionNocachedCall2()
    {
        $this->smarty->caching = 1;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('test_template_function_nocache_call.tpl');
        $this->assertTrue($this->smarty->isCached($tpl));
        $tpl->assign('foo', 'bar');
        $this->assertContains('bar bar', $this->smarty->fetch($tpl));
    }
}
