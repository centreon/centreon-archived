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
    \CentreonConfiguration\Models\Connector;

class ConnectorTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $newConnector = array(
            'name' => 'TestConnector',
            'description' => 'My connector test',
            'command_line' => '$CONNECTORS$/my_connector',
            'enabled' => 1,
            'created' => 1407836372,
            'modified' => 1407836372
        );
        Connector::insert($newConnector);
        /* Assert for test insert in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/connector.insert.xml'
        )->getTable('connector');
        $tableResult = $this->getConnection()->createQueryTable(
            'connector',
            'SELECT * FROM connector'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Connector::insert($newConnector);
    }

    public function testDelete()
    {
        Connector::delete(2);
        /* Assert for test delete in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/connector.delete.xml'
        )->getTable('connector');
        $tableResult = $this->getConnection()->createQueryTable(
            'connector',
            'SELECT * FROM connector'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Connector::delete(42);
    }

    public function testUpdate()
    {
        $newInformation = array(
            'description' => 'Connector for SSH',
            'enabled' => 0
        );
        Connector::update(2, $newInformation);
        /* Assert for test update in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/connector.update.xml'
        )->getTable('connector');
        $tableResult = $this->getConnection()->createQueryTable(
            'connector',
            'SELECT * FROM connector'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testUpdateNotUnique()
    {
        /* Test exception unique */
        $newInformation = array(
            'name' => 'Perl'
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Connector::update(2, $newInformation);
    }

    public function testUpdateNotFound() {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        $newInformation = array(
            'name' => 'Perl'
        );
        Connector::update(42, $newInformation);
    }

    public function testDuplicate()
    {
        Connector::duplicate(1);
        /* Assert for test duplicate 1 in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/connector.duplicate-1.xml'
        )->getTable('connector');
        $tableResult = $this->getConnection()->createQueryTable(
            'connector',
            'SELECT * FROM connector'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        Connector::duplicate(2, 2);
        /* Assert for test duplicate 2 in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/connector.duplicate-2.xml'
        )->getTable('connector');
        $tableResult = $this->getConnection()->createQueryTable(
            'connector',
            'SELECT * FROM connector'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Connector::duplicate(42);
    }

    public function testGetParameters()
    {
        $testInformation = array(
            'id' => 1,
            'name' => 'Perl',
            'description' => 'Connector for embeded perl',
            'command_line' => '$CONNECTORS$/perl',
            'enabled' => 1,
            'created' => 1407836372,
            'modified' => 1407836372
        );
        $connector = Connector::getParameters(1, '*');

        $this->assertEquals($connector, $testInformation);

        $connector = Connector::getParameters(2, 'name');
        $this->assertEquals($connector, array('name' => 'SSH'));

        $connector = Connector::getParameters(2, array('name', 'enabled'));
        $this->assertEquals($connector, array('name' => 'SSH', 'enabled' => 1));
    }

    public function testGetParametersNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        $connector = Connector::getParameters(3, '*');
    }

    public function testGetParametersBadColumns()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Connector::getParameters(1, 'test_error');

        Connector::getParameters(1, array('name', 'test_error'));
    }

    public function testGetList()
    {
        $testResult = array(
            array(
                'id' => 1,
                'name' => 'Perl',
                'description' => 'Connector for embeded perl',
                'command_line' => '$CONNECTORS$/perl',
                'enabled' => 1,
                'created' => 1407836372,
                'modified' => 1407836372
            ),
            array(
                'id' => 2,
                'name' => 'SSH',
                'description' => 'Connector for SSH connections',
                'command_line' => '$CONNECTORS$/ssh',
                'enabled' => 1,
                'created' => 1407836372,
                'modified' => 1407836372
            )
        );
        $result = Connector::getList();
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array(
                'id' => 1,
                'name' => 'Perl',
                'description' => 'Connector for embeded perl',
                'command_line' => '$CONNECTORS$/perl',
                'enabled' => 1,
                'created' => 1407836372,
                'modified' => 1407836372
            )
        );
        $result = Connector::getList('*', 1);
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array(
                'id' => 2,
                'name' => 'SSH',
                'description' => 'Connector for SSH connections',
                'command_line' => '$CONNECTORS$/ssh',
                'enabled' => 1,
                'created' => 1407836372,
                'modified' => 1407836372
            )
        );
        $result = Connector::getList('*', 1, 1);
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array('name' => 'Perl'),
            array('name' => 'SSH')
        );
        $result = Connector::getList('name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Perl', 'id' => 1),
            array('name' => 'SSH', 'id' => 2)
        );
        $result = Connector::getList(array('name', 'id'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'SSH'),
            array('name' => 'Perl')
        );
        $result = Connector::getList('name', -1, 0, 'name', 'DESC');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'SSH')
        );
        $result = Connector::getList('name', -1, 0, null, 'ASC', array('name' => 'SSH'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Perl'),
            array('name' => 'SSH')
        );
        $result = Connector::getList('name', -1, 0, null, 'ASC', array('name' => array('SSH', 'Perl')));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Unknown filter type",
            0
        );
        Connector::getList('name', -1, 0, null, 'ASC', array('name' => array('SSH', 'Perl')), 'ERR');
    }

    public function testGetListBySearch()
    {
        $testResult = array(
            array('name' => 'Perl'),
            array('name' => 'SSH')
        );
        $result = Connector::getListBySearch('name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Perl'),
            array('name' => 'SSH')
        );
        $result = Connector::getListBySearch('name', -1, 0, null, 'ASC', array('description' => 'Connector'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Perl'),
            array('name' => 'SSH')
        );
        $result = Connector::getListBySearch('name', -1, 0, 'name', 'ASC', array('description' => array('SSH', 'Perl')));
        $this->assertEquals($testResult, $result);
    }

    public function testGet()
    {
        $testResult = array(
            'id' => 1,
            'name' => 'Perl',
            'description' => 'Connector for embeded perl',
            'command_line' => '$CONNECTORS$/perl',
            'enabled' => 1,
            'created' => 1407836372,
            'modified' => 1407836372
        );
        $result = Connector::get(1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'name' => 'Perl',
        );
        $result = Connector::get(1, 'name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'name' => 'Perl',
            'description' => 'Connector for embeded perl',
        );
        $result = Connector::get(1, array('name', 'description'));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Connector::get(42);
    }

    public function testGetIdByParameter()
    {
        $testResult = array(1);
        $result = Connector::getIdByParameter('name', 'Perl');
        $this->assertEquals($testResult, $result);

        $testResult = array(1, 2);
        $result = Connector::getIdByParameter('name', array('Perl', 'SSH'));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Connector::getIdByParameter('errColumn', 'Perl');
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('id', Connector::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('name', Connector::getUniqueLabelField());
    }

    public function getRelations()
    {
        $this->assertEquals(array(), Connector::getRelations());
    }

    public function testIsUnique()
    {
        $this->assertTrue(Connector::isUnique('Perl', 1));
        $this->assertFalse(Connector::isUnique('Perl', 2));
        $this->assertFalse(Connector::isUnique('Perl'));
    }

    public function testGetTableName()
    {
        $this->assertEquals('connector', Connector::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'id',
                'name',
                'description',
                'command_line',
                'enabled',
                'created',
                'modified'
            ),
            Connector::getColumns()
        );
    }
}
