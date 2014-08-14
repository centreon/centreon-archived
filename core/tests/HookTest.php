<?php

namespace Test\Centreon;

use \Centreon\Internal\Hook;

class HookTest extends DbTestCase
{
    protected static $bootstrapExtraSteps = array('actionHooks');

    public function tearDown()
    {
        parent::tearDown();
        Hook::reset();
    }

    public function testGetHookId()
    {
        $this->assertEquals(1, Hook::getHookId('displayLeftMenu'));
    }

    public function testGetHookIdWithUnknownName()
    {
        $this->setExpectedException(
            "\Centreon\Internal\Exception",
            "Could not find hook named idontexist"
        );
        Hook::getHookId('idontexist');
    }

    public function testGetHookName()
    {
        $this->assertEquals('displayLeftMenu', Hook::getHookName(1));
    }

    public function testGetHookNameWithUnknownId()
    {
        $this->setExpectedException(
            "\Centreon\Internal\Exception",
            "Could not find hook id 9999"
        );
        Hook::getHookName(9999);
    }

    public function testRegister()
    {
        Hook::register(
            1,                 // moduleId
            'displayLeftMenu', // hookName
            'displayTest',     // moduleHookName
            'a description'    // moduleHookDescription
        );
        $this->tableEqualsXml(
            'module_hooks',
            dirname(__DIR__) . '/tests/data/hook.register.xml'
        );
    }

    public function testRegisterException()
    {
        Hook::register(
            1,                 
            'displayLeftMenu',
            'displayTest',
            'a description'
        );
        $this->setExpectedException(
            "\Centreon\Internal\Exception",
            "Hook already registered"
        );
        Hook::register(
            1,                
            'displayLeftMenu',
            'displayTest',
            'a description'
        );
    }

    public function testUnregister()
    {
        Hook::unregister(
            6,
            'displayLeftMenu',
            'displayBookmarkedViews'
        );
        $this->assertEquals(0, $this->getConnection()->getRowCount('module_hooks'));
    }

    public function testUnregisterException()
    {

    }

    public function testGetModulesFromHook()
    {

    }

    public function testGetModulesFromHookWithHookType()
    {

    }

    public function testGetModulesFromHookWithHookName()
    {

    }

    public function testGetModulesFromWithHookTypeAndHookName()
    {

    }

    public function testExecute()
    {

    }
}
