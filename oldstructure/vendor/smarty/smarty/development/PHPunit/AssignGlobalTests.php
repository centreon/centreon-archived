<?php
/**
* Smarty PHPunit tests assignGlobal methode  and {assignGlobal} tag
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for assignGlobal methode  and {assignGlobal} tag tests
*/
class AssignGlobalTests extends PHPUnit_Framework_TestCase
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
    * test  assignGlobal and getGlobal
    */
    public function testAssignGlobalGetGlobal()
    {
        $this->smarty->assignGlobal('foo', 'bar');
        $this->assertEquals('bar', $this->smarty->getGlobal('foo'));
    }
    /**
    * test  assignGlobal and getGlobal on arrays
    */
    public function testAssignGlobalGetGlobalArray()
    {
        $this->smarty->assignGlobal('foo', array('foo' => 'bar', 'foo2' => 'bar2'));
        $a1 = array('foo' => array('foo' => 'bar', 'foo2' => 'bar2'));
        $a2 = $this->smarty->getGlobal();
        $diff = array_diff($a1, $a2);
        $cmp = empty($diff);
        $this->assertTrue($cmp);
    }
    /**
    * test assignGlobal tag
    */
    public function testAssignGlobalTag()
    {
        $this->smarty->assignGlobal('foo', 'bar');
        $this->assertEquals('bar', $this->smarty->fetch('eval:{$foo}'));
        $this->assertEquals('buh', $this->smarty->fetch('eval:{assign var=foo value=buh scope=global}{$foo}'));
        $this->assertEquals('buh', $this->smarty->fetch('eval:{$foo}'));
        $this->assertEquals('buh', $this->smarty->getGlobal('foo'));
    }
    /**
    * test global var array element tag
    */
    public function testGlobalVarArrayTag()
    {
        $this->smarty->assignGlobal('foo', array('foo' => 'bar', 'foo2' => 'bar2'));
        $this->assertEquals('bar2', $this->smarty->fetch('eval:{$foo.foo2}'));
        $this->assertEquals('bar', $this->smarty->fetch('eval:{$foo.foo}'));
    }
}
