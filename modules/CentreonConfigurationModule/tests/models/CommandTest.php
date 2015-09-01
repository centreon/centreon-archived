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

namespace Test\CentreonConfiguration\Models;

require_once CENTREON_PATH . "/tests/DbTestCase.php";

use \Test\Centreon\DbTestCase,
    \CentreonConfiguration\Models\Command;

class CommandTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $newCommand = array(
            'command_name' => "My check",
            'command_line' => '$USER1$/bin/my_check -w $ARGV1$',
            'command_example' => '$USER1$/bin/my_check -w 90',
            'command_type' => 2,
            'enable_shell' => 0,
            'command_comment' => "My check command",
            'organization_id' => 1
        );
        Command::insert($newCommand);
        /* Assert for test insert in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/command.insert.xml'
        )->getTable('cfg_commands');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_commands',
            'SELECT * FROM cfg_commands'
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
        )->getTable('cfg_commands');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_commands',
            'SELECT * FROM cfg_commands'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testDeleteNotFound()
    {
        /* Test exception object doesn't exists */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
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
        )->getTable('cfg_commands');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_commands',
            'SELECT * FROM cfg_commands'
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
            $this->errMsg,
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
        )->getTable('cfg_commands');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_commands',
            'SELECT * FROM cfg_commands'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        Command::duplicate(2, 2);
        /* Assert for test duplicate 2 in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/command.duplicate-2.xml'
        )->getTable('cfg_commands');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_commands',
            'SELECT * FROM cfg_commands'
        );
        $this->assertTablesEqual($dataset, $tableResult);

    }

    public function testDuplicateNotFound() {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
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
            'cmd_cat_id' => null,
            'organization_id' => 1
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
            $this->errMsg,
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
                'cmd_cat_id' => null,
                'organization_id' => 1
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
                'cmd_cat_id' => null,
                'organization_id' => 1
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
                'cmd_cat_id' => null,
                'organization_id' => 1
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
                'cmd_cat_id' => null,
                'organization_id' => 1
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
                'cmd_cat_id' => null,
                'organization_id' => 1
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
            array('command_name' => 'Test check', 'command_id' => 2),
            array('command_name' => 'Test connector', 'command_id' => 3),
            array('command_name' => 'Test notif', 'command_id' => 1)
        );
        $result = Command::getList(array('command_name', 'command_id'));
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
            'cmd_cat_id' => null,
            'organization_id' => 1
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
            $this->errMsg,
            0
        );
        Command::get(42);
    }

    public function testGetIdByParameter()
    {
        $testResult = array(1);
        $result = Command::getIdByParameter('command_name', 'Test notif');
        $this->assertEquals($testResult, $result);

        $testResult = array(2, 1);
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
        $this->assertEquals('cfg_commands', Command::getTableName());
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
                'cmd_cat_id',
                'organization_id'
            ),
            Command::getColumns()
        );
    }
}
