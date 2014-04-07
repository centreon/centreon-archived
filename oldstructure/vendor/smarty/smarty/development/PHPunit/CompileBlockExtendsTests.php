<?php
/**
* Smarty PHPunit tests for Block Extends
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for block extends compiler tests
*/
class CompileBlockExtendsTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->setTemplateDir(array('./templates/compileblockextends/','./templates/'));
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
    * test block default outout
    */
    public function testBlockDefault_000_1()
    {
        $result = $this->smarty->fetch('eval:{block name=test}-- block default --{/block}');
        $this->assertEquals('-- block default --', $result);
    }

    public function testBlockDefault_000_2()
    {
        $this->smarty->assign ('foo', 'another');
        $result = $this->smarty->fetch('eval:{block name=test}-- {$foo} block default --{/block}');
        $this->assertEquals('-- another block default --', $result);
    }
    /**
    * test just call of  parent template, no blocks predefined
    */
    public function testCompileBlockParent_001()
    {
        $result = $this->smarty->fetch('001_parent.tpl');
        $this->assertContains('Default Title', $result);
    }
    /**
    * test  child/parent template chain
    */
    public function testCompileBlockChild_002()
    {
        $result = $this->smarty->fetch('002_child.tpl');
        $this->assertContains('Page Title', $result);
    }
    /**
    * test  child/parent template chain with prepend
    */
    public function testCompileBlockChildPrepend_003()
    {
        $result = $this->smarty->fetch('003_child_prepend.tpl');
        $this->assertContains("prepend - Default Title", $result);
    }
    /**
    * test  child/parent template chain with apppend
    */
    public function testCompileBlockChildAppend_004()
    {
        $result = $this->smarty->fetch('004_child_append.tpl');
        $this->assertContains("Default Title - append", $result);
    }
    /**
    * test  child/parent template chain with apppend and shorttags
    */
    public function testCompileBlockChildAppendShortag_005()
    {
        $result = $this->smarty->fetch('005_child_append_shorttag.tpl');
        $this->assertContains("Default Title - append", $result);
    }
    /**
    * test  child/parent template chain with {$this->smarty.block.child)
    */
    public function testCompileBlockChildSmartyChild_006()
    {
        $result = $this->smarty->fetch('006_child_smartychild.tpl');
        $this->assertContains('here is >child text< included', $result);
    }
    /**
    * test  child/parent template chain with {$this->smarty.block.parent)
    */
    public function testCompileBlockChildSmartyParent_007()
    {
        $result = $this->smarty->fetch('007_child_smartyparent.tpl');
        $this->assertContains('parent block Default Title is here', $result);
    }
    /**
    * test  child/parent template chain loading plugin
    */
    public function testCompileBlockChildPlugin_008()
    {
        $result = $this->smarty->fetch('008_child_plugin.tpl');
        $this->assertContains('escaped &lt;text&gt;', $result);
    }
    /**
    * test parent template with nested blocks
    */
    public function testCompileBlockParentNested_009()
    {
        $result = $this->smarty->fetch('009_parent_nested.tpl');
        $this->assertContains('Title with -default- here', $result);
    }
    /**
    * test  child/parent template chain with nested block
    */
    public function testCompileBlockChildNested_010()
    {
        $result = $this->smarty->fetch('010_child_nested.tpl');
        $this->assertContains('Title with -content from child- here', $result);
    }
    /**
    * test  child/parent template chain with nested block and include
    */
    public function testCompileBlockChildNestedInclude_011()
    {
        $result = $this->smarty->fetch('011_grandchild_nested_include.tpl');
        $this->assertContains('hello world', $result);
    }
    /**
    * test  grandchild/child/parent template chain
    */
    public function testCompileBlockGrandChild_012()
    {
        $result = $this->smarty->fetch('012_grandchild.tpl');
        $this->assertContains('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent template chain prepend
    */
    public function testCompileBlockGrandChildPrepend_013()
    {
        $result = $this->smarty->fetch('013_grandchild_prepend.tpl');
        $this->assertContains('grandchild prepend - Page Title', $result);
    }
    /**
    * test  grandchild/child/parent template chain with {$this->smarty.block.child}
    */
    public function testCompileBlockGrandChildSmartyChild_014()
    {
        $result = $this->smarty->fetch('014_grandchild_smartychild.tpl');
        $this->assertContains('child title with - grandchild content - here', $result);
    }
    /**
    * test  grandchild/child/parent template chain append
    */
    public function testCompileBlockGrandChildAppend_015()
    {
        $result = $this->smarty->fetch('015_grandchild_append.tpl');
        $this->assertContains('Page Title - grandchild append', $result);
    }
    /**
    * test  grandchild/child/parent template chain with nested block
    */
    public function testCompileBlockGrandChildNested_016()
    {
        $result = $this->smarty->fetch('016_grandchild_nested.tpl');
        $this->assertContains('child title with -grandchild content- here', $result);
    }
    /**
    * test  grandchild/child/parent template chain with nested {$this->smarty.block.child}
    */
    public function testCompileBlockGrandChildNested_017()
    {
        $result = $this->smarty->fetch('017_grandchild_nested.tpl');
        $this->assertContains('child pre -grandchild content- child post', $result);
    }
    /**
    * test  nested child block with hide
    */
    public function testCompileBlockChildNestedHide_018()
    {
        $result = $this->smarty->fetch('018_child_nested_hide.tpl');
        $this->assertContains('nested block', $result);
        $this->assertNotContains('should be hidden', $result);
    }
    /**
    * test  nested child block with hide and auto_literal = false
    */
    public function testCompileBlockChildNestedHideAutoLiteralFalse_019()
    {
        $this->smarty->auto_literal = false;
        $result = $this->smarty->fetch('019_child_nested_hide_autoliteral.tpl');
        $this->assertContains('nested block', $result);
        $this->assertNotContains('should be hidden', $result);
    }
    /**
    * test  child/parent template chain starting in subtempates
    */
    public function testCompileBlockStartSubTemplates_020()
    {
        $result = $this->smarty->fetch('020_include_root.tpl');
        $this->assertContains('page 1', $result);
        $this->assertContains('page 2', $result);
        $this->assertContains('page 3', $result);
        $this->assertContains('block 1', $result);
        $this->assertContains('block 2', $result);
        $this->assertContains('block 3', $result);
   }
    /**
    * test  grandchild/child/parent dependency test1
    */
    public function testCompileBlockGrandChildMustCompile_021_1()
    {
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $this->assertFalse($tpl->isCached());
        $result = $this->smarty->fetch($tpl);
        $this->assertContains('Grandchild Page Title', $result);
        $this->smarty->template_objects = null;
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $this->assertTrue($tpl2->isCached());
        $result = $this->smarty->fetch($tpl2);
        $this->assertContains('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent dependency test2
    */
    public function testCompileBlockGrandChildMustCompile_021_2()
    {
        touch($this->smarty->getTemplateDir(0) . '021_grandchild.tpl');
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $this->assertFalse($tpl->isCached());
        $result = $this->smarty->fetch($tpl);
        $this->assertContains('Grandchild Page Title', $result);
        $this->smarty->template_objects = null;
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $this->assertTrue($tpl2->isCached());
        $result = $this->smarty->fetch($tpl2);
        $this->assertContains('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent dependency test3
    */
    public function testCompileBlockGrandChildMustCompile_021_3()
    {
        touch($this->smarty->getTemplateDir(0) . '021_child.tpl');
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $this->assertFalse($tpl->isCached());
        $result = $this->smarty->fetch($tpl);
        $this->assertContains('Grandchild Page Title', $result);
        $this->smarty->template_objects = null;
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $this->assertTrue($tpl2->isCached());
        $result = $this->smarty->fetch($tpl2);
        $this->assertContains('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent dependency test4
    */
    public function testCompileBlockGrandChildMustCompile_021_4()
    {
        touch($this->smarty->getTemplateDir(0) . '021_parent.tpl');
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $this->assertFalse($tpl->isCached());
        $result = $this->smarty->fetch($tpl);
        $this->assertContains('Grandchild Page Title', $result);
        $this->smarty->template_objects = null;
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $this->assertTrue($tpl2->isCached());
        $result = $this->smarty->fetch($tpl2);
        $this->assertContains('Grandchild Page Title', $result);
    }

    /**
     * test dirt in child templates
     */
    public function testDirt_022()
    {
        $result = $this->smarty->fetch('022_child.tpl');
        $this->assertEquals('Page Title', $result);
    }

    /**
     * test {strip} outside {block]
     */
    public function testChildStrip_023()
    {
        $result = $this->smarty->fetch('023_child.tpl');
        $this->assertContains('<div id="header"><div>Demo</div></div>', $result);
    }

    /**
     * test {$this->smarty.block.child} for not existing child {block]
     */
    public function testNotExistingChildBlock_024()
    {
        $result = $this->smarty->fetch('024_parent.tpl');
        $this->assertContains('no >< child', $result);
    }

    /**
     * test {$this->smarty.block.child} outside {block]
     */
    public function testSmartyBlockChildOutsideBlock_025()
    {
        try {
            $result = $this->smarty->fetch('025_parent.tpl');
        } catch (Exception $e) {
            $this->assertContains('used outside', $e->getMessage());

            return;
        }
        $this->fail('Exception for {$this->smarty.block.child} used outside {block} has not been raised.');
    }

    /**
     * test {$this->smarty.block.parent} outside {block]
     */
    public function testSmartyBlockParentOutsideBlock_026()
    {
        try {
            $result = $this->smarty->fetch('026_parent.tpl');
        } catch (Exception $e) {
            $this->assertContains('used outside', $e->getMessage());

            return;
        }
        $this->fail('Exception for {$this->smarty.block.parent} used outside {block} has not been raised.');
    }

    /**
     * test {$this->smarty.block.parent} in parent template
     */
    public function testSmartyBlockParentInParent_027()
    {
        try {
            $result = $this->smarty->fetch('027_parent.tpl');
        } catch (Exception $e) {
            $this->assertContains('in parent template', $e->getMessage());

            return;
        }
        $this->fail('Exception for illegal {$this->smarty.block.parent} in parent template has not been raised.');
    }

    /**
     * test variable file name in {extends}
     */
    public function testVariableExtends_028()
    {
        $this->smarty->assign('foo','028_parent.tpl');
        try {
            $result = $this->smarty->fetch('028_child.tpl');
        } catch (Exception $e) {
            $this->assertContains('variable template file name not allowed', $e->getMessage());

            return;
        }
        $this->fail('Exception for illegal variable template file name not been raised.');
    }

    /**
     * test variable file name in {include}
     */
    public function testVariableExtends_029()
    {
        $this->smarty->assign('foo','helloworld.tpl');
        try {
            $result = $this->smarty->fetch('029_parent.tpl');
        } catch (Exception $e) {
            $this->assertContains('variable template file names not allow within {block} tags', $e->getMessage());

            return;
        }
        $this->fail('Exception for illegal variable template file name not been raised.');
    }

    /**
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
        $result = $this->smarty->fetch('030_grandchild.tpl');
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
        $result = $this->smarty->fetch('030_grandchild.tpl');
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
