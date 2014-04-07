<?php
/**
* Smarty PHPunit tests for tag attributes
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for tag attribute tests
*/
class AttributeTests extends PHPUnit_Framework_TestCase
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
    * test required attribute
    */
    public function testRequiredAttributeVar()
    {
        try {
            $this->smarty->fetch('string:{assign value=1}');
        } catch (Exception $e) {
            $this->assertContains('missing "var" attribute', $e->getMessage());

            return;
        }
        $this->fail('Exception for required attribute "var" has not been raised.');
    }
    /**
    * test unexspected attribute
    */
    public function testUnexpectedAttribute()
    {
        try {
            $this->smarty->fetch('string:{assign var=foo value=1 bar=2}');
        } catch (Exception $e) {
            $this->assertContains('unexpected "bar" attribute', $e->getMessage());

            return;
        }
        $this->fail('Exception for unexpected attribute "bar" has not been raised.');
    }
    /**
    * test illegal option value
    */
    public function testIllegalOptionValue()
    {
        try {
            $this->smarty->fetch('string:{assign var=foo value=1 nocache=buh}');
        } catch (Exception $e) {
            $this->assertContains(htmlentities('illegal value of option flag'), $e->getMessage());

            return;
        }
        $this->fail('Exception for illegal value of option flag has not been raised.');
    }
    /**
    * test too many shorthands
    */
    public function testTooManyShorthands()
    {
        try {
            $this->smarty->fetch('string:{assign foo 1 2}');
        } catch (Exception $e) {
            $this->assertContains('too many shorthand attributes', $e->getMessage());

            return;
        }
        $this->fail('Exception for too many shorthand attributes has not been raised.');
    }
}
