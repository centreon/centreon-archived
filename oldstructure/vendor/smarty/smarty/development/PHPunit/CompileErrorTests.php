<?php
/**
* Smarty PHPunit tests compiler errors
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for compiler tests
*/
class CompileErrorTests extends PHPUnit_Framework_TestCase
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
    * test none existing template file error
    */
    public function testNoneExistingTemplateError()
    {
        try {
            $this->smarty->fetch('eval:{include file=\'no.tpl\'}');
        } catch (Exception $e) {
            $this->assertContains('Unable to load template', $e->getMessage());

            return;
        }
        $this->fail('Exception for none existing template has not been raised.');
    }
    /**
    * test unkown tag error
    */
    public function testUnknownTagError()
    {
        try {
            $this->smarty->fetch('eval:{unknown}');
        } catch (Exception $e) {
            $this->assertContains('unknown tag "unknown"', $e->getMessage());

            return;
        }
        $this->fail('Exception for unknown Smarty tag has not been raised.');
    }
    /**
    * test unclosed tag error
    */
    public function testUnclosedTagError()
    {
        try {
            $this->smarty->fetch('eval:{if true}');
        } catch (Exception $e) {
            $this->assertContains('unclosed {if} tag', $e->getMessage());

            return;
        }
        $this->fail('Exception for unclosed Smarty tags has not been raised.');
    }
    /**
    * test syntax error
    */
    public function testSyntaxError()
    {
        try {
            $this->smarty->fetch('eval:{assign var=}');
        } catch (Exception $e) {
            $this->assertContains('Syntax error in template "599a9cf0e3623a3206bd02a0f5c151d5f5f3f69e"', $e->getMessage());
            $this->assertContains('Unexpected "}"', $e->getMessage());

            return;
        }
        $this->fail('Exception for syntax error has not been raised.');
    }
    /**
    * test empty templates
    */
    public function testEmptyTemplate()
    {
        $tpl = $this->smarty->createTemplate('eval:');
        $this->assertEquals('', $this->smarty->fetch($tpl));
    }

}
