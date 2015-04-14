<?php
/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
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
