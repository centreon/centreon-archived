<?php

namespace Test\Centreon;

use \Centreon\Internal\Hook,
    \Centreon\Internal\Di;

class HookTest extends DbTestCase
{
    protected static $bootstrapExtraSteps = array('actionHooks', 'template');

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
            dirname(__DIR__) . '/tests/data/hook.register.xml',
            true
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
        $this->setExpectedException(
            "\Centreon\Internal\Exception",
            "Could not find module hook named idontexist"
        );
        Hook::unregister(
            6,
            'displayLeftMenu',
            'idontexist'
        );
        $this->setExpectedException(
            "\Centreon\Internal\Exception",
            "Could not find module hook named idontexist"
        );
        Hook::unregister(
            9999,
            'displayLeftMenu',
            'displayBookmarkedViews'
        );
        $this->setExpectedException(
            "\Centreon\Internal\Exception",
            "Could not find module hook named idontexist"
        );
        Hook::unregister(
            6,
            'idontexist',
            'displayBookmarkedViews'
        ); 
    }

    public function testGetModulesFromHook()
    {
        $arr = Hook::getModulesFromHook();
        $this->assertEquals(1, count($arr));
    }

    public function testGetModulesFromHookWithHookTypeDisplay()
    {
        $arr = Hook::getModulesFromHook(Hook::TYPE_DISPLAY);
        $this->assertEquals(1, count($arr));
    }

    public function testGetModulesFromHookWithHookTypeAction()
    {
        $arr = Hook::getModulesFromHook(Hook::TYPE_ACTION);
        $this->assertEquals(0, count($arr));
    }

    public function testGetModulesFromHookWithUnknownHookType()
    {
        $this->setExpectedException(
            "\Centreon\Internal\Exception",
            "Unknown hook type 9999"
        );
        $arr = Hook::getModulesFromHook(9999);
    }

    public function testGetModulesFromHookWithHookName()
    {
        $arr = Hook::getModulesFromHook(null, 'displayLeftMenu');
        $this->assertEquals(1, count($arr));
    }

    public function testGetModulesFromHookWithUnknownHookName()
    {
        $arr = Hook::getModulesFromHook(null, 'idontexist');
        $this->assertEquals(0, count($arr));
    }

    public function testGetModulesFromWithHookTypeAndHookName()
    {
        $arr = Hook::getModulesFromHook(Hook::TYPE_DISPLAY, 'displayLeftMenu');
        $this->assertEquals(1, count($arr));
    }

    public function testExecuteDisplayHook()
    {
        ob_start();
        Di::getDefault()->get('router')->dispatch();
        ob_end_clean();
        $result = Hook::execute('displayLeftMenu', array('test_key' => 'test_value'));
        $this->assertEquals(1, count($result));
    }

    public function testExecuteInvalidDisplayHook()
    {
        ob_start();
        Di::getDefault()->get('router')->dispatch();
        ob_end_clean();
        $this->setExpectedException(
            "\Centreon\Internal\Exception",
            "Invalid hook name idontexist"
        );
        $result = Hook::execute('idontexist', array('test_key' => 'test_value'));
    }

    public function testExecuteEmptyDisplayHook()
    {
        ob_start();
        Di::getDefault()->get('router')->dispatch();
        ob_end_clean();
        $result = Hook::execute('displayIdontexist', array('test_key' => 'test_value'));
        $this->assertEquals(0, count($result));
    }
}
