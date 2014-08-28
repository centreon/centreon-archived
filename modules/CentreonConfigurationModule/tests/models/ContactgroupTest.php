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
    \CentreonConfiguration\Models\Contactgroup;

class ContactgroupTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $newContactgroup = array(
            'cg_name' => 'Contactgroup test',
            'cg_alias' => 'Contactgroup test',
            'cg_comment' => 'Test contactgroup',
            'cg_activate' => '1'
        );
        Contactgroup::insert($newContactgroup);
        /* Assert for test insert in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/contactgroup.insert.xml'
        )->getTable('cfg_contactgroups');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_contactgroups',
            'SELECT * FROM cfg_contactgroups'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Contactgroup::insert($newContactgroup);
    }

    public function testDelete()
    {
        Contactgroup::delete(2);
        /* Assert for test delete in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/contactgroup.delete.xml'
        )->getTable('cfg_contactgroups');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_contactgroups',
            'SELECT * FROM cfg_contactgroups'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Contactgroup::delete(42);
    }

    public function testUpdate()
    {
        $newInformation = array(
            'cg_comment' => 'Modified comment',
            'cg_activate' => '1'
        );
        Contactgroup::update(2, $newInformation);
        /* Assert for test update in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/contactgroup.update.xml'
        )->getTable('cfg_contactgroups');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_contactgroups',
            'SELECT * FROM cfg_contactgroups'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testUpdateNotUnique()
    {
        /* Test exception unique */
        $newInformation = array(
            'cg_name' => 'Contactgroup 1'
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Contactgroup::update(2, $newInformation);
    }

    public function testUpdateNotFound() {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        $newInformation = array(
            'cg_name' => 'Contactgroup 1'
        );
        Contactgroup::update(42, $newInformation);
    }

    public function testDuplicate()
    {
        Contactgroup::duplicate(1);
        /* Assert for test duplicate 1 in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/contactgroup.duplicate-1.xml'
        )->getTable('cfg_contactgroups');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_contactgroups',
            'SELECT * FROM cfg_contactgroups'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        Contactgroup::duplicate(2, 2);
        /* Assert for test duplicate 2 in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/contactgroup.duplicate-2.xml'
        )->getTable('cfg_contactgroups');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_contactgroups',
            'SELECT * FROM cfg_contactgroups'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Contactgroup::duplicate(42);
    }

    public function testGetParameters()
    {
        $testInformation = array(
            'cg_id' => 1,
            'cg_name' => 'Contactgroup 1',
            'cg_alias' => 'Contactgroup 1',
            'cg_comment' => 'First contactgroup',
            'cg_activate' => '1',
            'cg_type' => 'local',
            'cg_ldap_dn' => null,
            'ar_id' => null
        );
        $contactgroup = Contactgroup::getParameters(1, '*');

        $this->assertEquals($contactgroup, $testInformation);

        $contactgroup = Contactgroup::getParameters(2, 'cg_name');
        $this->assertEquals($contactgroup, array('cg_name' => 'Contactgroup 2'));

        $contactgroup = Contactgroup::getParameters(2, array('cg_name', 'cg_activate'));
        $this->assertEquals($contactgroup, array('cg_name' => 'Contactgroup 2', 'cg_activate' => '0'));
    }

    public function testGetParametersNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        $connector = Contactgroup::getParameters(42, '*');
    }

    public function testGetParametersBadColumns()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Contactgroup::getParameters(1, 'test_error');
    }

    public function testGetList()
    {
        $testResult = array(
            array(
                'cg_id' => 1,
                'cg_name' => 'Contactgroup 1',
                'cg_alias' => 'Contactgroup 1',
                'cg_comment' => 'First contactgroup',
                'cg_activate' => '1',
                'cg_type' => 'local',
                'cg_ldap_dn' => null,
                'ar_id' => null
            ),
            array(
                'cg_id' => 2,
                'cg_name' => 'Contactgroup 2',
                'cg_alias' => 'Contactgroup 2',
                'cg_comment' => 'Second contactgroup',
                'cg_activate' => '0',
                'cg_type' => 'local',
                'cg_ldap_dn' => null,
                'ar_id' => null
            )
        );
        $result = Contactgroup::getList();
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array(
                'cg_id' => 1,
                'cg_name' => 'Contactgroup 1',
                'cg_alias' => 'Contactgroup 1',
                'cg_comment' => 'First contactgroup',
                'cg_activate' => '1',
                'cg_type' => 'local',
                'cg_ldap_dn' => null,
                'ar_id' => null
            )
        );
        $result = Contactgroup::getList('*', 1);
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array(
                'cg_id' => 2,
                'cg_name' => 'Contactgroup 2',
                'cg_alias' => 'Contactgroup 2',
                'cg_comment' => 'Second contactgroup',
                'cg_activate' => '0',
                'cg_type' => 'local',
                'cg_ldap_dn' => null,
                'ar_id' => null
            )
        );
        $result = Contactgroup::getList('*', 1, 1);
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array('cg_name' => 'Contactgroup 1'),
            array('cg_name' => 'Contactgroup 2')
        );
        $result = Contactgroup::getList('cg_name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('cg_name' => 'Contactgroup 1', 'cg_id' => 1),
            array('cg_name' => 'Contactgroup 2', 'cg_id' => 2)
        );
        $result = Contactgroup::getList(array('cg_name', 'cg_id'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('cg_name' => 'Contactgroup 2'),
            array('cg_name' => 'Contactgroup 1')
        );
        $result = Contactgroup::getList('cg_name', -1, 0, 'cg_name', 'DESC');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('cg_name' => 'Contactgroup 2')
        );
        $result = Contactgroup::getList('cg_name', -1, 0, null, 'ASC', array('cg_name' => 'Contactgroup 2'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('cg_name' => 'Contactgroup 1'),
            array('cg_name' => 'Contactgroup 2')
        );
        $result = Contactgroup::getList('cg_name', -1, 0, null, 'ASC', array('cg_name' => array('Contactgroup 1', 'Contactgroup 2')));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Unknown filter type",
            0
        );
        Contactgroup::getList('cg_name', -1, 0, null, 'ASC', array('cg_name' => array('SSH', 'Perl')), 'ERR');
    }

    public function testGetListBySearch()
    {
        $testResult = array(
            array('cg_name' => 'Contactgroup 1'),
            array('cg_name' => 'Contactgroup 2')
        );
        $result = Contactgroup::getListBySearch('cg_name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('cg_name' => 'Contactgroup 1')
        );
        $result = Contactgroup::getListBySearch('cg_name', -1, 0, null, 'ASC', array('cg_comment' => 'First'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('cg_name' => 'Contactgroup 1'),
            array('cg_name' => 'Contactgroup 2')
        );
        $result = Contactgroup::getListBySearch('cg_name', -1, 0, 'cg_name', 'ASC', array('cg_comment' => array('First', 'Second')));
        $this->assertEquals($testResult, $result);
    }

    public function testGet()
    {
        $testResult = array(
            'cg_id' => 1,
            'cg_name' => 'Contactgroup 1',
            'cg_alias' => 'Contactgroup 1',
            'cg_comment' => 'First contactgroup',
            'cg_activate' => '1',
            'cg_type' => 'local',
            'cg_ldap_dn' => null,
            'ar_id' => null
        );
        $result = Contactgroup::get(1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'cg_name' => 'Contactgroup 1',
        );
        $result = Contactgroup::get(1, 'cg_name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'cg_name' => 'Contactgroup 1',
            'cg_comment' => 'First contactgroup'
        );
        $result = Contactgroup::get(1, array('cg_name', 'cg_comment'));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Contactgroup::get(42);
    }

    public function testGetIdByParameter()
    {
        $testResult = array(1);
        $result = Contactgroup::getIdByParameter('cg_name', 'Contactgroup 1');
        $this->assertEquals($testResult, $result);

        $testResult = array(1, 2);
        $result = Contactgroup::getIdByParameter('cg_name', array('Contactgroup 1', 'Contactgroup 2'));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Contactgroup::getIdByParameter('errColumn', 'Perl');
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('cg_id', Contactgroup::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('cg_name', Contactgroup::getUniqueLabelField());
    }

    public function getRelations()
    {
        $this->assertEquals(array(), Contactgroup::getRelations());
    }

    public function testIsUnique()
    {
        $this->assertTrue(Contactgroup::isUnique('Contactgroup 1', 1));
        $this->assertFalse(Contactgroup::isUnique('Contactgroup 1', 2));
        $this->assertFalse(Contactgroup::isUnique('Contactgroup 1'));
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_contactgroups', Contactgroup::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'cg_id',
                'cg_name',
                'cg_alias',
                'cg_comment',
                'cg_activate',
                'cg_type',
                'cg_ldap_dn',
                'ar_id'
            ),
            Contactgroup::getColumns()
        );
    }
}
