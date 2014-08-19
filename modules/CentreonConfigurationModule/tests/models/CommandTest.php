<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace Test\CentreonConfiguration\Models;

require_once CENTREON_PATH . "/tests/DbTestCase.php";

use \Test\Centreon\DbTestCase,
    \CentreonConfiguration\Models\Command;

class CommandTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $newCommand = array(
            'command_name' => "My check",
            'command_line' => '$USER1$/bin/my_check -w $ARGV1$',
            'command_example' => '$USER1$/bin/my_check -w 90',
            'command_type' => 2,
            'enable_shell' => 0,
            'command_comment' => "My check command"
        );
        Command::insert($newCommand);
        /* Assert for test insert in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/command.insert.xml'
        )->getTable('command');
        $tableResult = $this->getConnection()->createQueryTable(
            'command',
            'SELECT * FROM command'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testInsertNotUnique()
    {
        $newCommand = array(
            'command_name' => "Test check",
            'command_line' => '$USER1$/bin/my_check -w $ARGV1$',
            'command_example' => '$USER1$/bin/my_check -w 90',
            'command_type' => 2,
            'enable_shell' => 0,
            'command_comment' => "My check command"
        );
        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Command::insert($newCommand);
    }

    public function testInsertConnectorNotExists()
    {
        $newCommand = array(
            'command_name' => "My check",
            'connector_id' => 42,
            'command_line' => '$USER1$/bin/my_check -w $ARGV1$',
            'command_example' => '$USER1$/bin/my_check -w 90',
            'command_type' => 2,
            'enable_shell' => 0,
            'command_comment' => "My check command"
        );
        /* Test connector does not exists */
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Command::insert($newCommand);
    }

    public function testDelete()
    {
        Command::delete(2);
        /* Assert for test delete in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/command.delete.xml'
        )->getTable('command');
        $tableResult = $this->getConnection()->createQueryTable(
            'command',
            'SELECT * FROM command'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testDeleteNotFound()
    {
        /* Test exception object doesn't exists */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        Command::delete(42);
    }

    public function testUpdate()
    {
        $newInformation = array(
            'command_comment' => 'Check ping',
            'enable_shell' => 1
        );
        Command::update(2, $newInformation);
        /* Assert for test update in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/command.update.xml'
        )->getTable('command');
        $tableResult = $this->getConnection()->createQueryTable(
            'command',
            'SELECT * FROM command'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testUpdateNotUnique()
    {
        /* Test exception unique */
        $newInformation = array(
            'command_name' => 'Test notif'
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Command::update(2, $newInformation);
    }

    public function testUpdateNotFound() {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        $newInformation = array(
            'command_name' => 'Command'
        );
        Command::update(42, $newInformation);
    }

    public function testUpdateConnectorNotExists()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        $newInformation = array(
            'connector_id' => 42
        );
        Command::update(2, $newInformation);
    }

    public function testDuplicate()
    {
        Command::duplicate(1);
        /* Assert for test duplicate 1 in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/command.duplicate-1.xml'
        )->getTable('command');
        $tableResult = $this->getConnection()->createQueryTable(
            'command',
            'SELECT * FROM command'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        Command::duplicate(2, 2);
        /* Assert for test duplicate 2 in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/command.duplicate-2.xml'
        )->getTable('command');
        $tableResult = $this->getConnection()->createQueryTable(
            'command',
            'SELECT * FROM command'
        );
        $this->assertTablesEqual($dataset, $tableResult);

    }

    public function testDuplicateNotFound() {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "The object doesn't exist in database.",
            0
        );
        Command::duplicate(42);
    }

    public function testGetParameters()
    {
        $testInformation = array(
            'command_id' => 1,
            'connector_id' => null,
            'command_name' => 'Test notif',
            'command_line' => '/usr/bin/mail -s $ARG1$',
            'command_example' => "/usr/bin/mail -s 'Subject'",
            'command_type' => 1,
            'enable_shell' => 1,
            'command_comment' => 'Notification : send a mail',
            'graph_id' => null,
            'cmd_cat_id' => null
        );
        $connector = Command::getParameters(1, '*');

        $this->assertEquals($connector, $testInformation);

        $connector = Command::getParameters(2, 'command_name');
        $this->assertEquals($connector, array('command_name' => 'Test check'));

        $connector = Command::getParameters(2, array('command_name', 'enable_shell'));
        $this->assertEquals($connector, array('command_name' => 'Test check', 'enable_shell' => 0));
    }

    public function testGetParametersNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "The object doesn't exist in database.",
            0
        );
        $connector = Command::getParameters(42, '*');
    }

    public function testGetParametersBadColumns()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Command::getParameters(1, 'test_error');
    }

    public function testGetParametersBadColumnsMultiple()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Command::getParameters(1, array('name', 'test_error'));
    }

    public function testGetList()
    {
        $testResult = array(
            array(
                'command_id' => 1,
                'connector_id' => null,
                'command_name' => 'Test notif',
                'command_line' => '/usr/bin/mail -s $ARG1$',
                'command_example' => "/usr/bin/mail -s 'Subject'",
                'command_type' => 1,
                'enable_shell' => 1,
                'command_comment' => 'Notification : send a mail',
                'graph_id' => null,
                'cmd_cat_id' => null
            ),
            array(
                'command_id' => 2,
                'connector_id' => null,
                'command_name' => 'Test check',
                'command_line' => '$USER1$/check_ping -w $ARG1$ -c $ARG2$',
                'command_example' => '$USER1$/check_ping -w 1 -c 2',
                'command_type' => 2,
                'enable_shell' => 0,
                'command_comment' => 'Check : check ping',
                'graph_id' => null,
                'cmd_cat_id' => null
            ),
            array(
                'command_id' => 3,
                'connector_id' => 1,
                'command_name' => 'Test connector',
                'command_line' => '$USER1$/check_connector.pl -w $ARG1$ -c $ARG2$',
                'command_example' => '$USER1$/check_connector.pl -w 80 -c 90',
                'command_type' => 2,
                'enable_shell' => 0,
                'command_comment' => 'Check : check connector',
                'graph_id' => null,
                'cmd_cat_id' => null
            )
        );
        $result = Command::getList();
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array(
                'command_id' => 1,
                'connector_id' => null,
                'command_name' => 'Test notif',
                'command_line' => '/usr/bin/mail -s $ARG1$',
                'command_example' => "/usr/bin/mail -s 'Subject'",
                'command_type' => 1,
                'enable_shell' => 1,
                'command_comment' => 'Notification : send a mail',
                'graph_id' => null,
                'cmd_cat_id' => null
            )
        );
        $result = Command::getList('*', 1);
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array(
                'command_id' => 2,
                'connector_id' => null,
                'command_name' => 'Test check',
                'command_line' => '$USER1$/check_ping -w $ARG1$ -c $ARG2$',
                'command_example' => '$USER1$/check_ping -w 1 -c 2',
                'command_type' => 2,
                'enable_shell' => 0,
                'command_comment' => 'Check : check ping',
                'graph_id' => null,
                'cmd_cat_id' => null
            )
        );
        $result = Command::getList('*', 1, 1);
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array('command_name' => 'Test check'),
            array('command_name' => 'Test connector'),
            array('command_name' => 'Test notif')
        );
        $result = Command::getList('command_name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('command_name' => 'Test notif'),
            array('command_name' => 'Test connector'),
            array('command_name' => 'Test check')
        );
        $result = Command::getList('command_name', -1, 0, 'command_name', 'DESC');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('command_name' => 'Test check')
        );
        $result = Command::getList('command_name', -1, 0, null, 'ASC', array('command_name' => 'Test check'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('command_name' => 'Test check'),
            array('command_name' => 'Test connector')
        );
        $result = Command::getList('command_name', -1, 0, null, 'ASC', array('command_name' => array('Test connector', 'Test check')));
        $this->assertEquals($testResult, $result);
    }
    
    public function testGetListBadFilter()
    {

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Unknown filter type",
            0
        );
        Command::getList('command_name', -1, 0, null, 'ASC', array('command_name' => array('Test connector', 'Test check')), 'ERR');
    }

    public function testGetListBySearch()
    {
        $testResult = array(
            array('command_name' => 'Test check'),
            array('command_name' => 'Test connector'),
            array('command_name' => 'Test notif')
        );
        $result = Command::getListBySearch('command_name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('command_name' => 'Test check'),
            array('command_name' => 'Test connector')
        );
        $result = Command::getListBySearch('command_name', -1, 0, null, 'ASC', array('command_name' => 'Test', 'enable_shell' => 0), 'AND');
        $this->assertEquals($testResult, $result);
    }

    public function testGet()
    {
        $testResult = array(
            'command_id' => 1,
            'connector_id' => null,
            'command_name' => 'Test notif',
            'command_line' => '/usr/bin/mail -s $ARG1$',
            'command_example' => "/usr/bin/mail -s 'Subject'",
            'command_type' => 1,
            'enable_shell' => 1,
            'command_comment' => 'Notification : send a mail',
            'graph_id' => null,
            'cmd_cat_id' => null
        );
        $result = Command::get(1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'command_name' => 'Test notif',
        );
        $result = Command::get(1, 'command_name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'command_name' => 'Test notif',
            'command_comment' => 'Notification : send a mail'
        );
        $result = Command::get(1, array('command_name', 'command_comment'));
        $this->assertEquals($testResult, $result);
    }

    public function testGetNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "The object doesn't exist in database.",
            0
        );
        Command::get(42);
    }

    public function testGetIdByParameter()
    {
        $testResult = array(1);
        $result = Command::getIdByParameter('command_name', 'Test notif');
        $this->assertEquals($testResult, $result);

        $testResult = array(1, 2);
        $result = Command::getIdByParameter('command_name', array('Test notif', 'Test check'));
        $this->assertEquals($testResult, $result);
    }

    public function testGetIdByParameterBadColumn()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Command::getIdByParameter('errColumn', 'Test notif');
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('command_id', Command::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('command_name', Command::getUniqueLabelField());
    }

    public function getRelations()
    {
        $this->assertEquals(array(), Command::getRelations());
    }

    public function testIsUnique()
    {
        $this->assertTrue(Command::isUnique('Test notif', 1));
        $this->assertFalse(Command::isUnique('Test notif', 2));
        $this->assertFalse(Command::isUnique('Test notif'));
    }

    public function testGetTableName()
    {
        $this->assertEquals('command', Command::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'command_id',
                'connector_id',
                'command_name',
                'command_line',
                'command_example',
                'command_type',
                'enable_shell',
                'command_comment',
                'graph_id',
                'cmd_cat_id'
            ),
            Command::getColumns()
        );
    }
}
