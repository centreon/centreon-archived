<?php
/**
* Smarty PHPunit tests object variables
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for object variable tests
*/
class ObjectVariableTests extends PHPUnit_Framework_TestCase
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
    * test simple object variable
    */
    public function testObjectVariableOutput()
    {
        $object = new VariableObject;
        $tpl = $this->smarty->createTemplate('string:{$object->hello}');
        $tpl->assign('object', $object);
        $this->assertEquals('hello_world', $this->smarty->fetch($tpl));
    }
    /**
    * test simple object variable with variable property
    */
    public function testObjectVariableOutputVariableProperty()
    {
        $object = new VariableObject;
        $this->smarty->disableSecurity();
        $tpl = $this->smarty->createTemplate('string:{$p=\'hello\'}{$object->$p}');
        $tpl->assign('object', $object);
        $this->assertEquals('hello_world', $this->smarty->fetch($tpl));
    }
    /**
    * test simple object variable with method
    */
    public function testObjectVariableOutputMethod()
    {
        $object = new VariableObject;
        $tpl = $this->smarty->createTemplate('string:{$object->myhello()}');
        $tpl->assign('object', $object);
        $this->assertEquals('hello world', $this->smarty->fetch($tpl));
    }
    /**
    * test simple object variable with method
    */
    public function testObjectVariableOutputVariableMethod()
    {
        $object = new VariableObject;
        $this->smarty->disableSecurity();
        $tpl = $this->smarty->createTemplate('string:{$p=\'myhello\'}{$object->$p()}');
        $tpl->assign('object', $object);
        $this->assertEquals('hello world', $this->smarty->fetch($tpl));
    }
    /**
    * test  object variable in double quoted string
    */
    public function testObjectVariableOutputDoubleQuotes()
    {
        $object = new VariableObject;
        $tpl = $this->smarty->createTemplate('string:{"double quoted `$object->hello` okay"}');
        $tpl->assign('object', $object);
        $this->assertEquals('double quoted hello_world okay', $this->smarty->fetch($tpl));
    }
    /**
    * test  object variable in double quoted string as include name
    */
    public function testObjectVariableOutputDoubleQuotesInclude()
    {
        $object = new VariableObject;
        $tpl = $this->smarty->createTemplate('string:{include file="`$object->hello`_test.tpl"}');
        $tpl->assign('object', $object);
        $this->assertEquals('hello world', $this->smarty->fetch($tpl));
    }
}

Class VariableObject {
    public $hello = 'hello_world';

    public function myhello()
    {
        return 'hello world';
    }
}
