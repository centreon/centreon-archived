<?php
/**
* Smarty PHPunit tests register_filter / unregister_filter methods
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for register_filter / unregister_filter methods tests
*/
class RegisterFilterTests extends PHPUnit_Framework_TestCase
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
    * test register->preFilter method for function
    */
    public function testRegisterPrefilterFunction()
    {
        $this->smarty->registerFilter(Smarty::FILTER_PRE,'myfilter');
        $this->assertTrue(is_callable($this->smarty->registered_filters['pre']['myfilter']));
    }
    /**
    * test register->preFilter method for class methode
    */
    public function testRegisterPrefilterMethode()
    {
        $this->smarty->registerFilter(Smarty::FILTER_PRE,array('myfilterclass','execute'));
        $this->assertTrue(is_callable($this->smarty->registered_filters['pre']['myfilterclass_execute']));
    }
    /**
    * test register->preFilter method for class object
    */
    public function testRegisterPrefilterObject()
    {
        $this->smarty->registerFilter(Smarty::FILTER_PRE,array(new myfilterclass,'execute'));
        $this->assertTrue(is_callable($this->smarty->registered_filters['pre']['myfilterclass_execute']));
    }
    /**
    * test unregister->preFilter method for function
    */
    public function testUnegisterPrefilterFunction()
    {
        $this->smarty->registerFilter(Smarty::FILTER_PRE,'myfilter');
        $this->smarty->unregisterFilter(Smarty::FILTER_PRE,'myfilter');
        $this->assertFalse(isset($this->smarty->registered_filters['pre']['myfilter']));
    }
    /**
    * test unregister->preFilter method for class methode
    */
    public function testUnregisterPrefilterMethode()
    {
        $this->smarty->registerFilter(Smarty::FILTER_PRE,array('myfilterclass','execute'));
        $this->smarty->unregisterFilter(Smarty::FILTER_PRE,array('myfilterclass','execute'));
        $this->assertFalse(isset($this->smarty->registered_filters['pre']['myfilterclass_execute']));
    }
    /**
    * test register->postFilter method for function
    */
    public function testRegisterPostfilterFunction()
    {
        $this->smarty->registerFilter(Smarty::FILTER_POST,'myfilter');
        $this->assertTrue(is_callable($this->smarty->registered_filters['post']['myfilter']));
    }
    /**
    * test register->postFilter method for class methode
    */
    public function testRegisterPostfilterMethode()
    {
        $this->smarty->registerFilter(Smarty::FILTER_POST,array('myfilterclass','execute'));
        $this->assertTrue(is_callable($this->smarty->registered_filters['post']['myfilterclass_execute']));
    }
    /**
    * test unregister->postFilter method for function
    */
    public function testUnegisterPostfilterFunction()
    {
        $this->smarty->registerFilter(Smarty::FILTER_POST,'myfilter');
        $this->smarty->unregisterFilter(Smarty::FILTER_POST,'myfilter');
        $this->assertFalse(isset($this->smarty->registered_filters['post']['myfilter']));
    }
    /**
    * test unregister->postFilter method for class methode
    */
    public function testUnregisterPostfilterMethode()
    {
        $this->smarty->registerFilter(Smarty::FILTER_POST,array('myfilterclass','execute'));
        $this->smarty->unregisterFilter(Smarty::FILTER_POST,array('myfilterclass','execute'));
        $this->assertFalse(isset($this->smarty->registered_filters['post']['myfilterclass_execute']));
    }
    /**
    * test register->outputFilter method for function
    */
    public function testRegisterOutputfilterFunction()
    {
        $this->smarty->registerFilter(Smarty::FILTER_OUTPUT,'myfilter');
        $this->assertTrue(is_callable($this->smarty->registered_filters['output']['myfilter']));
    }
    /**
    * test register->outputFilter method for class methode
    */
    public function testRegisterOutputfilterMethode()
    {
        $this->smarty->registerFilter(Smarty::FILTER_OUTPUT,array('myfilterclass','execute'));
        $this->assertTrue(is_callable($this->smarty->registered_filters['output']['myfilterclass_execute']));
    }
    /**
    * test unregister->outputFilter method for function
    */
    public function testUnegisterOutputfilterFunction()
    {
        $this->smarty->registerFilter(Smarty::FILTER_OUTPUT,'myfilter');
        $this->smarty->unregisterFilter(Smarty::FILTER_OUTPUT,'myfilter');
        $this->assertFalse(isset($this->smarty->registered_filters['output']['myfilter']));
    }
    /**
    * test unregister->outputFilter method for class methode
    */
    public function testUnregisterOutputfilterMethode()
    {
        $this->smarty->registerFilter(Smarty::FILTER_OUTPUT,array('myfilterclass','execute'));
        $this->smarty->unregisterFilter(Smarty::FILTER_OUTPUT,array('myfilterclass','execute'));
        $this->assertFalse(isset($this->smarty->registered_filters['output']['myfilterclass_execute']));
    }
}
function myfilter($input)
{
    return $input;
}
class myfilterclass
{
    static function execute($input)
    {
        return $input;
    }
}
