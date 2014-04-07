<?php
/**
* Smarty PHPunit tests compilation of the {include} tag
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for {include} tests
*/
class CompileIncludeTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->force_compile = true;
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * test standard output
    */
    public function testIncludeStandard()
    {
        $tpl = $this->smarty->createTemplate('eval:{include file="helloworld.tpl"}');
        $content = $this->smarty->fetch($tpl);
        $this->assertEquals("hello world", $content);
    }
    /**
    * Test that assign attribute does not create standard output
    */
    public function testIncludeAssign1()
    {
        $tpl = $this->smarty->createTemplate('eval:{include file="helloworld.tpl" assign=foo}');
        $this->assertEquals("", $this->smarty->fetch($tpl));
    }
    /**
    * Test that assign attribute does load variable
    */
    public function testIncludeAssign2()
    {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=bar}{include file="helloworld.tpl" assign=foo}{$foo}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    /**
    * Test passing local vars
    */
    public function testIncludePassVars()
    {
        $tpl = $this->smarty->createTemplate("eval:{include file='eval:{\$myvar1}{\$myvar2}' myvar1=1 myvar2=2}");
        $this->assertEquals("12", $this->smarty->fetch($tpl));
    }
    /**
    * Test local scope
    */
    public function testIncludeLocalScope()
    {
        $this->smarty->assign('foo',1);
        $tpl = $this->smarty->createTemplate('eval: befor include {$foo} {include file=\'eval:{$foo=2} in include {$foo}\'} after include {$foo}', null, null, $this->smarty);
        $content = $this->smarty->fetch($tpl);
        $this->assertContains('befor include 1', $content);
        $this->assertContains('in include 2', $content);
        $this->assertContains('after include 1', $content);
    }
    /**
    * Test  parent scope
    */
    public function testIncludeParentScope()
    {
        $this->smarty->assign('foo',1);
        $tpl = $this->smarty->createTemplate('eval: befor include {$foo} {include file=\'eval:{$foo=2} in include {$foo}\' scope = parent} after include {$foo}', null, null, $this->smarty);
        $content = $this->smarty->fetch($tpl);
        $content2 = $this->smarty->fetch('eval: root value {$foo}' );
        $this->assertContains('befor include 1', $content);
        $this->assertContains('in include 2', $content);
        $this->assertContains('after include 2', $content);
        $this->assertContains('root value 1', $content2);
    }
    /**
    * Test  root scope
    */
    public function testIncludeRootScope()
    {
         $this->smarty->error_reporting  = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
        $this->smarty->assign('foo',1);
        $tpl = $this->smarty->createTemplate('eval: befor include {$foo} {include file=\'eval:{$foo=2} in include {$foo}\' scope = root} after include {$foo}');
        $content = $this->smarty->fetch($tpl);
        $content2 = $this->smarty->fetch('eval: smarty value {$foo}' );
        $this->assertNotContains('befor include 1', $content);
        $this->assertContains('in include 2', $content);
        $this->assertContains('after include 2', $content);
        $this->assertContains('smarty value 1', $content2);
    }
    /**
    * Test  root scope
    */
    public function testIncludeRootScope2()
    {
        $this->smarty->assign('foo',1);
        $tpl = $this->smarty->createTemplate('eval: befor include {$foo} {include file=\'eval:{$foo=2} in include {$foo}\' scope = root} after include {$foo}', null, null, $this->smarty);
        $content = $this->smarty->fetch($tpl);
        $content2 = $this->smarty->fetch('eval: smarty value {$foo}' );
        $this->assertContains('befor include 1', $content);
        $this->assertContains('in include 2', $content);
        $this->assertContains('after include 1', $content);
        $this->assertContains('smarty value 2', $content2);
    }
    /**
    * Test  recursive includes
    */
    public function testRecursiveIncludes1()
    {
        $this->smarty->assign('foo',1);
        $this->smarty->assign('bar','bar');
        $content = $this->smarty->fetch('test_recursive_includes.tpl');
        $this->assertContains("before 1 bar<br>\nbefore 2 bar<br>\nbefore 3 bar<br>\nafter 3 bar<br>\nafter 2 bar<br>\nafter 1 bar<br>", $content);
    }
    public function testRecursiveIncludes2()
    {
        $this->smarty->assign('foo',1);
        $this->smarty->assign('bar','bar');
        $content = $this->smarty->fetch('test_recursive_includes2.tpl');
        $this->assertContains("before 1 bar<br>\nbefore 3 bar<br>\nbefore 5 bar<br>\nafter 5 bar<br>\nafter 3 bar<br>\nafter 1 bar<br>", $content);
    }
}
