<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */


namespace Test\Centreon;

use Centreon\Internal\Hook,
    \Centreon\Internal\Di;

class HookTest extends DbTestCase
{
    protected static $bootstrapExtraSteps = array('Events', 'template');

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
            'cfg_modules_hooks',
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
        $this->assertEquals(0, $this->getConnection()->getRowCount('cfg_modules_hooks'));
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
