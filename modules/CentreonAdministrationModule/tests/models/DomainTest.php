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
 * this program; if not, see <htenv://www.gnu.org/licenses>.
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
 */

namespace Test\CentreonAdministration\Models;

use \Test\Centreon\DbTestCase;
use CentreonAdministration\Models\Domain;

class DomainTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonAdministrationModule/tests/data/json/';

    public function testInsert()
    {
        $envInsert = array(
            'name' => 'test_name',
            'description' => 'test_description',
            'isroot' => '0',
            'parent_id' => '4'
        );
        Domain::insert($envInsert);
        $this->tableEqualsXml(
            'cfg_domains',
            dirname(__DIR__) . '/data/domain.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $envInsert = array(
            'name' => 'test_name',
            'description' => 'test_description',
            'isroot' => '0',
            'parent_id' => '4'
        );
        Domain::insert($envInsert);
        $this->setExpectedException(
            'PDOException'
        );
        Domain::insert($envInsert);
    }

    public function testDelete()
    {
        Domain::delete(1);
        $this->tableEqualsXml(
            'cfg_domains',
            dirname(__DIR__) . '/data/domain.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Domain::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'description' => 'new_description'
        );
        Domain::update(1, $newInfo);
        $this->tableEqualsXml(
            'cfg_domains',
            dirname(__DIR__) . '/data/domain.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'name' => 'Network'
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Domain::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
        $newInfo = array(
            'name' => 'Test'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Domain::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        Domain::duplicate(1);
        $this->tableEqualsXml(
            'cfg_domains',
            dirname(__DIR__) . '/data/domain.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        Domain::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_domains',
            dirname(__DIR__) . '/data/domain.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Domain::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Domain::duplicate(9999, 2);
    }

    public function testGetAllParameters()
    {
        $testInformation = array(
            'domain_id' => '2',
            'name' => 'Hardware',
            'description' => 'Hardware domain',
            'isroot' => '1',
            'parent_id' => null,
            'icon_id' => null
        );
        $arr = Domain::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'name' => 'Hardware',
        );
        $arr = Domain::getParameters(2, 'name');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'name' => 'Hardware',
            'description' => 'Hardware domain',
            'isroot' => '1'
        );
        $arr = Domain::getParameters(2, array('name', 'description', 'isroot'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Domain::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Domain::getParameters(9999, 'name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Domain::getParameters(9999, array('name', 'description'));
    }

    public function testGetUnknownParameters()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Domain::getParameters(2, 'idontexist');

        $this->setExpectedException(
            'PDOException'
        );
        Domain::getParameters(2, array('name', 'idontexist'));
    }

    public function testGetList()
    {
        $expectedResult = array(
            array(
                'domain_id' => '1',
                'name' => 'Network',
                'description' => 'Network domain',
                'isroot' => '1',
                'parent_id' => null,
                'icon_id' => null
            ),
            array(
                'domain_id' => '2',
                'name' => 'Hardware',
                'description' => 'Hardware domain',
                'isroot' => '1',
                'parent_id' => null,
                'icon_id' => null
            ),
            array(
                'domain_id' => '3',
                'name' => 'System',
                'description' => 'System domain',
                'isroot' => '1',
                'parent_id' => null,
                'icon_id' => null
            ),
            array(
                'domain_id' => '4',
                'name' => 'Application',
                'description' => 'Application domain',
                'isroot' => '1',
                'parent_id' => null,
                'icon_id' => null
            ),
        );
        $this->assertEquals($expectedResult, Domain::getList());
    }

    public function testGetListLimitOne()
    {
        $expectedResult = array(
            array(
                'domain_id' => '1',
                'name' => 'Network',
                'description' => 'Network domain',
                'isroot' => '1',
                'parent_id' => null,
                'icon_id' => null
            )
        );
        $this->assertEquals($expectedResult, Domain::getList('*', 1));
    }

    public function testGetListSecondElementOnly()
    {
        $expectedResult = array(
            array(
                'domain_id' => '2',
                'name' => 'Hardware',
                'description' => 'Hardware domain',
                'isroot' => '1',
                'parent_id' => null,
                'icon_id' => null
            )
        );
        $this->assertEquals($expectedResult, Domain::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('name' => 'Application'),
            array('name' => 'Hardware'),
            array('name' => 'Network'),
            array('name' => 'System')
        );
        $this->assertEquals($expectedResult, Domain::getList('name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('domain_id' => '4', 'name' => 'Application'),
            array('domain_id' => '2', 'name' => 'Hardware'),
            array('domain_id' => '1', 'name' => 'Network'),
            array('domain_id' => '3', 'name' => 'System')
        );
        $this->assertEquals($expectedResult, Domain::getList(array('domain_id', 'name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('name' => 'Application'),
            array('name' => 'System'),
            array('name' => 'Hardware'),
            array('name' => 'Network'),
        );
        $this->assertEquals($expectedResult, Domain::getList('name', null, null, 'domain_id', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('name' => 'Hardware')
        );
        $this->assertEquals(
            $expectedResult,
            Domain::getList(
                'name',
                null,
                null,
                null,
                null,
                array(
                    'name' => 'Hardware'
                )
            )
        );
    }

    public function testGetListWithMultipleFilters()
    {
        $expectedResult = array(
            array('name' => 'Hardware')
        );
        $this->assertEquals(
            $expectedResult,
            Domain::getList(
                'name',
                null,
                null,
                null,
                null,
                array(
                    'name' => 'Hardware',
                    'domain_id' => 2
                )
            )
        );
    }

    public function testGetListWithFilterNoResult()
    {
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult,
            Domain::getList(
                'name',
                null,
                null,
                null,
                null,
                array(
                    'name' => 'idontexist',
                )
            )
        );
    }

    public function testGetListBySearch()
    {
        $expectedResult = array(
            array('name' => 'Hardware')
        );
        $this->assertEquals(
            $expectedResult,
            Domain::getListBySearch(
                'name',
                null,
                null,
                null,
                null,
                array('name' => 'ware')
            )
        );
    }

    public function testGet()
    {
        $expectedResult = array(
            'domain_id' => '2',
            'name' => 'Hardware',
            'description' => 'Hardware domain',
            'isroot' => '1',
            'parent_id' => null,
            'icon_id' => null
        );
        $this->assertEquals($expectedResult, Domain::get(2));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'name' => 'Network'
        );
        $this->assertEquals($expectedResult, Domain::get(1, 'name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'name' => 'Network',
            'isroot' => '1'
        );
        $this->assertEquals($expectedResult, Domain::get(1, array('name', 'isroot')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Domain::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array('2');
        $this->assertEquals($expectedResult, Domain::getIdByParameter('name', 'Hardware'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array('2', '1');
        $this->assertEquals($expectedResult, Domain::getIdByParameter('name', array('Network', 'Hardware')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Domain::getIdByParameter('idontexist', array('Network'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('domain_id', Domain::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('name', Domain::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_domains', Domain::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'domain_id',
                'name',
                'description',
                'isroot',
                'parent_id',
                'icon_id'
            ),
            Domain::getColumns()
        );
    }
}
