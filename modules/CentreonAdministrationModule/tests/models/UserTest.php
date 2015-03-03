<?php
/*
 * Copyright 2005-2014 CENTREON
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
 * For more information : user@centreon.com
 *
 */

namespace Test\CentreonAdministration\Models;

require_once CENTREON_PATH . "/tests/DbTestCase.php";

use Test\Centreon\DbTestCase;
use CentreonAdministration\Models\User;

class UserTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonAdministrationModule/tests/data/json/';

    public function testInsert()
    {
        $newUser = array(
            'firstname' => 'New user',
            'login' => 'New user',
            'password' => 'wxcvb',
            'language_id' => 'en_US',
            'is_activated' => '1',
            'auth_type' => 'local'
        );
        User::insert($newUser);
        /* Assert for test insert in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/user.insert.xml'
        )->getTable('cfg_users');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_users',
            'SELECT * FROM cfg_users'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testInsertDuplicate()
    {
        $newUser = array(
            'firstname' => 'User 1',
            'login' => 'User 1',
            'password' => 'wxcvb',
            'language_id' => 'us_US',
            'is_activated' => '1',
            'auth_type' => 'local'
        );

        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        User::insert($newUser);
    }

    public function testInsertTemplateNotExists()
    {
        $newUser = array(
            'timeperiod_tp_id' => 1,
            'timeperiod_tp_id2' => 1,
            'firstname' => 'New user',
            'login' => 'New user',
            'password' => 'wxcvb',
            'language_id' => 'en_US',
            'user_host_notification_options' => 'n',
            'user_service_notification_options' => 'n',
            'user_email' => 'user@domain.tld',
            'user_enable_notifications' => '1',
            'is_activated' => '1',
            'auth_type' => 'local',
            'user_register' => '1',
            'user_template_id' => 42
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        User::insert($newUser);
    }

    public function testDelete()
    {
        User::delete(3);
        /* Assert for test delete in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/user.delete.xml'
        )->getTable('cfg_users');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_users',
            'SELECT * FROM cfg_users'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testDeleteNotExists()
    {
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        User::delete(42);
    }

    public function testUpdate()
    {
        $newInformation = array(
            'user_comment' => 'New comment',
            'is_admin' => '0'
        );
        User::update(2, $newInformation);
        /* Assert for test update in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/user.update.xml'
        )->getTable('cfg_users');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_users',
            'SELECT * FROM cfg_users'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testUpdateNotUnique()
    {
        /* Test exception unique */
        $newInformation = array(
            'login' => 'User 1'
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        User::update(3, $newInformation);
    }

    public function testUpdateNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        $newInformation = array(
            'login' => 'User 1'
        );
        User::update(42, $newInformation);
    }

    public function testDuplicate()
    {
        User::duplicate(2);
        /* Assert for test duplicate 1 in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/user.duplicate-1.xml'
        )->getTable('cfg_users');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_users',
            'SELECT * FROM cfg_users'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        User::duplicate(3, 2);
        /* Assert for test duplicate 2 in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/user.duplicate-2.xml'
        )->getTable('cfg_users');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_users',
            'SELECT * FROM cfg_users'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testDuplicateNotExists()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        User::duplicate(42);
    }

    public function testGetParameters()
    {
        $testInformation = array(
            'user_id',
                'login',
                'password',
                'is_admin',
                'is_locked',
                'is_activated',
                'is_password_old',
                'language_id',
                'timezone_id',
                'contact_id',
                'createdat',
                'updatedat',
                'firstname',
                'lastname',
                'auth_type',
                'autologin_key'
        );
        $user = User::getParameters(1, '*');
        $this->assertEquals($user, $testInformation);

        $user = User::getParameters(2, 'login');
        $this->assertEquals($user, array('login' => 'User 1'));

        $user = User::getParameters(2, array('login', 'is_admin'));
        $this->assertEquals($user, array('login' => 'User 1', 'is_admin' => '1'));
    }

    public function testGetParametersNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        $user = User::getParameters(42, '*');
    }

    public function testGetParametersBadColumns()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        User::getParameters(1, 'test_error');

        User::getParameters(1, array('name', 'test_error'));
    }

    public function testGetList()
    {
        $testResult = array(
            array(
                'user_id',
                'login',
                'password',
                'is_admin',
                'is_locked',
                'is_activated',
                'is_password_old',
                'language_id',
                'timezone_id',
                'contact_id',
                'createdat',
                'updatedat',
                'firstname',
                'lastname',
                'auth_type',
                'autologin_key'
            ),
            array(
                'user_id',
                'login',
                'password',
                'is_admin',
                'is_locked',
                'is_activated',
                'is_password_old',
                'language_id',
                'timezone_id',
                'contact_id',
                'createdat',
                'updatedat',
                'firstname',
                'lastname',
                'auth_type',
                'autologin_key'
            ),
            array(
                'user_id',
                'login',
                'password',
                'is_admin',
                'is_locked',
                'is_activated',
                'is_password_old',
                'language_id',
                'timezone_id',
                'contact_id',
                'createdat',
                'updatedat',
                'firstname',
                'lastname',
                'auth_type',
                'autologin_key'
            )
        );
        $result = User::getList();
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'user_id',
                'login',
                'password',
                'is_admin',
                'is_locked',
                'is_activated',
                'is_password_old',
                'language_id',
                'timezone_id',
                'contact_id',
                'createdat',
                'updatedat',
                'firstname',
                'lastname',
                'auth_type',
                'autologin_key'
            )
        );
        $result = User::getList('*', 1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'user_id',
                'login',
                'password',
                'is_admin',
                'is_locked',
                'is_activated',
                'is_password_old',
                'language_id',
                'timezone_id',
                'contact_id',
                'createdat',
                'updatedat',
                'firstname',
                'lastname',
                'auth_type',
                'autologin_key'
            )
        );
        $result = User::getList('*', 1, 1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('login' => 'User 1'),
            array('login' => 'User 2'),
            array('login' => 'Template user')
        );
        $result = User::getList('login');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('login' => 'User 1', 'user_id' => 2),
            array('login' => 'User 2', 'user_id' => 3),
            array('login' => 'Template user', 'user_id' => 1)
        );
        $result = User::getList(array('login', 'user_id'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('login' => 'Template user'),
            array('login' => 'User 2'),
            array('login' => 'User 1')
        );
        $result = User::getList('login', -1, 0, 'login', 'DESC');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('login' => 'User 1')
        );
        $result = User::getList('login', -1, 0, null, 'ASC', array('login' => 'User 1'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('login' => 'User 1'),
            array('login' => 'User 2')
        );
        $result = User::getList('login', -1, 0, null, 'ASC', array('login' => array('User 1', 'User 2')));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Unknown filter type",
            0
        );
        User::getList('login', -1, 0, null, 'ASC', array('login' => array('SSH', 'Perl')), 'ERR');
    }

    public function testGetListBySearch()
    {
        $testResult = array(
            array('login' => 'User 1'),
            array('login' => 'User 2'),
            array('login' => 'Template user')
        );
        $result = User::getListBySearch('login');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('login' => 'User 1'),
            array('login' => 'User 2')
        );
        $result = User::getListBySearch('login', -1, 0, null, 'ASC', array('language_id' => 'us'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('login' => 'User 1'),
            array('login' => 'User 2'),
            array('login' => 'Template user')
        );
        $result = User::getListBySearch('login', -1, 0, 'login', 'ASC', array('language_id' => array('us', 'fr')));
        $this->assertEquals($testResult, $result);
    }

    public function testGet()
    {
        $testResult = array(
            'user_id',
                'login',
                'password',
                'is_admin',
                'is_locked',
                'is_activated',
                'is_password_old',
                'language_id',
                'timezone_id',
                'contact_id',
                'createdat',
                'updatedat',
                'firstname',
                'lastname',
                'auth_type',
                'autologin_key'
        );
        $result = User::get(1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'login' => 'User 1',
        );
        $result = User::get(2, 'login');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'login' => 'Template user',
            'user_comment' => 'Default template user'
        );
        $result = User::get(1, array('login', 'user_comment'));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        User::get(42);
    }

    public function testGetIdByParameter()
    {
        $testResult = array(2);
        $result = User::getIdByParameter('login', 'User 1');
        $this->assertEquals($testResult, $result);

        $testResult = array(2, 3);
        $result = User::getIdByParameter('login', array('User 1', 'User 2'));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        User::getIdByParameter('errColumn', 'Bad user');
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('user_id', User::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('login', User::getUniqueLabelField());
    }

    public function getRelations()
    {
        $this->assertEquals(array(), User::getRelations());
    }

    public function testIsUnique()
    {
        $this->assertTrue(User::isUnique('User 1', 2));
        $this->assertFalse(User::isUnique('User 1', 3));
        $this->assertFalse(User::isUnique('User 1'));
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_users', User::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'user_id',
                'login',
                'password',
                'is_admin',
                'is_locked',
                'is_activated',
                'is_password_old',
                'language_id',
                'timezone_id',
                'contact_id',
                'createdat',
                'updatedat',
                'firstname',
                'lastname',
                'auth_type',
                'autologin_key'
            ),
            User::getColumns()
        );
    }
}
