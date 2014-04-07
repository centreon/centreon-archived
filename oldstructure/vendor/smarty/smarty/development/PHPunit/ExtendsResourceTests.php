<?php
/**
* Smarty PHPunit tests for Extendsresource
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for extends resource tests
*/
class ExtendsResourceTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->setTemplateDir(array('./templates/extendsresource/','./templates/'));
//        $this->smarty->registerFilter(Smarty::FILTER_PRE,'prefilterextends');
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * clear folders
    */
    public function clear()
    {
        $this->smarty->clearAllCache();
        $this->smarty->clearCompiledTemplate();
    }
    /**
     * test  child/parent template chain with prepend
     */
    public function testCompileBlockChildPrepend_003()
    {
        $result = $this->smarty->fetch('extends:003_parent.tpl|003_child_prepend.tpl');
        $this->assertContains("prepend - Default Title", $result);
    }
    /**
     * test  child/parent template chain with apppend
     */
    public function testCompileBlockChildAppend_004()
    {
        $result = $this->smarty->fetch('extends:004_parent.tpl|004_child_append.tpl');
        $this->assertContains("Default Title - append", $result);
    }    /**
 * test nocache on different levels
 */
    public function testNocacheBlock_030_1()
    {
        $this->smarty->caching = 1;
        $this->smarty->assign('b1','b1_1');
        $this->smarty->assign('b3','b3_1');
        $this->smarty->assign('b4','b4_1');
        $this->smarty->assign('b5','b5_1');
        $this->smarty->assign('b6','b6_1');
        $result = $this->smarty->fetch('extends:030_parent.tpl|030_child.tpl|030_grandchild.tpl');
        $this->assertContains('parent b1 b1_1*parent b2*grandchild b3 b3_1*include b3 b6_1*grandchild b6 b6_1*', $result);
        $this->assertContains('child b4 b4_1*grandchild b4 b4_1**', $result);
        $this->assertContains('child b5 b5_1*grandchild b5 b5_1**', $result);
        $this->assertContains('child b61 b6_1*include 61 b6_1*grandchild b6 b6_1*', $result);
        $this->assertContains('child b62 b6_1*include 62 b6_1*grandchild b6 b6_1*', $result);
        $this->assertContains('child b63 b6_1*grandchild b6 b6_1*', $result);
        $this->assertContains('child b64 b6_1*include b64 b6_1*grandchild b6 b6_1*', $result);
        $this->assertContains('parent include b6_1*grandchild b6 b6_1*', $result);
        $this->assertContains('parent include2 grandchild b6 b6_1*', $result);
    }

    /**
     * test nocache on different levels
     */
    public function testNocacheBlock_030_2()
    {
        $this->smarty->caching = 1;
        $this->smarty->assign('b1','b1_2');
        $this->smarty->assign('b3','b3_2');
        $this->smarty->assign('b4','b4_2');
        $this->smarty->assign('b5','b5_2');
        $this->smarty->assign('b6','b6_2');
        $result = $this->smarty->fetch('extends:030_parent.tpl|030_child.tpl|030_grandchild.tpl');
        $this->assertContains('parent b1 b1_2*parent b2*grandchild b3 b3_2*include b3 b6_2*grandchild b6 b6_2*', $result);
        $this->assertContains('child b4 b4_1*grandchild b4 b4_2**', $result);
        $this->assertContains('child b5 b5_2*grandchild b5 b5_2**', $result);
        $this->assertContains('child b61 b6_1*include 61 b6_1*grandchild b6 b6_1*', $result);
        $this->assertContains('child b62 b6_2*include 62 b6_2*grandchild b6 b6_2*', $result);
        $this->assertContains('child b63 b6_1*grandchild b6 b6_2*', $result);
        $this->assertContains('child b64 b6_1*include b64 b6_2*grandchild b6 b6_2*', $result);
        $this->assertContains('parent include b6_2*grandchild b6 b6_2*', $result);
        $this->assertContains('parent include2 grandchild b6 b6_2*', $result);
    }

 }

function prefilterextends($input)
{
    return preg_replace('/{extends .*}/', '', $input);
}

