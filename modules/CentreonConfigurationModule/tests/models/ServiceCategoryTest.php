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

use \Test\Centreon\DbTestCase;
use CentreonConfiguration\Models\Servicecategory;

class ServiceCategoryTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $insert = array(
            'sc_name' => 'test',
            'sc_description' => 'test description',
            'sc_comment' => 'test comment',
            'sc_activate' => '1',
            'organization_id' => 1
        );
        Servicecategory::insert($insert);
        $this->tableEqualsXml(
            'cfg_servicecategories',
            dirname(__DIR__) . '/data/servicecategory.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $insert = array(
            'sc_name' => 'test',
            'sc_description' => 'test description',
            'sc_comment' => 'test comment',
            'sc_activate' => '1',
            'organization_id' => 1
        );
        Servicecategory::insert($insert);
        $this->setExpectedException(
            'PDOException'
        );
        Servicecategory::insert($insert);
    }

    public function testDelete()
    {
        Servicecategory::delete(2);
        $this->tableEqualsXml(
            'cfg_servicecategories',
            dirname(__DIR__) . '/data/servicecategory.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicecategory::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'sc_description' => 'new description'
        );
        Servicecategory::update(1, $newInfo);
        $this->tableEqualsXml(
            'cfg_servicecategories',
            dirname(__DIR__) . '/data/servicecategory.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'sc_name' => 'ping'
        );
        $this->setExpectedException(
            'PDOException'
        );
        Servicecategory::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
      $newInfo = array(
            'sc_name' => 'test'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicecategory::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        Servicecategory::duplicate(1);
        $this->tableEqualsXml(
            'cfg_servicecategories',
            dirname(__DIR__) . '/data/servicecategory.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        Servicecategory::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_servicecategories',
            dirname(__DIR__) . '/data/servicecategory.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicecategory::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicecategory::duplicate(9999, 2);
    }

    public function testGetAllParameters()
    {
        $testInformation = array(
            'sc_id' => 2,
            'sc_name' => 'storage',
            'sc_description' => 'storage',
            'level' => null,
            'icon_id' => null,
            'sc_comment' => 'Storage services',
            'sc_activate' => '1',
            'organization_id' => 1
        );
        $arr = Servicecategory::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'sc_name' => 'storage',
        );
        $arr = Servicecategory::getParameters(2, 'sc_name');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'sc_name' => 'storage',
            'sc_description' => 'storage',
            'sc_activate' => '1'
        );
        $arr = Servicecategory::getParameters(2, array('sc_name', 'sc_description', 'sc_activate'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicecategory::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicecategory::getParameters(9999, 'sc_name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicecategory::getParameters(9999, array('sc_name', 'sc_description'));
    }

    public function testGetUnknownParameters()
    {
       $this->setExpectedException(
           'PDOException'
       );
       Servicecategory::getParameters(2, 'idontexist');

       $this->setExpectedException(
           'PDOException'
       );
       Servicecategory::getParameters(2, array('sc_name', 'idontexist'));
    }

    public function testGetList()
    {
        $expectedResult = array(
            array(
                'sc_id' => 1,
                'sc_name' => 'ping',
                'sc_description' => 'ping',
                'level' => null,
                'icon_id' => null,
                'sc_comment' => 'Ping services',
                'sc_activate' => '1',
                'organization_id' => 1
            ),
            array(
                'sc_id' => 2,
                'sc_name' => 'storage',
                'sc_description' => 'storage',
                'level' => null,
                'icon_id' => null,
                'sc_comment' => 'Storage services',
                'sc_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Servicecategory::getList());
    }

    public function testGetListLimitOne()
    {
        $expectedResult = array(
            array(
                'sc_id' => 1,
                'sc_name' => 'ping',
                'sc_description' => 'ping',
                'level' => null,
                'icon_id' => null,
                'sc_comment' => 'Ping services',
                'sc_activate' => '1', 
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Servicecategory::getList('*', 1));
    }

    public function testGetListSecondElementOnly()
    {
        $expectedResult = array(
            array(
               'sc_id' => 2,
                'sc_name' => 'storage',
                'sc_description' => 'storage',
                'level' => null,
                'icon_id' => null,
                'sc_comment' => 'Storage services',
                'sc_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Servicecategory::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('sc_name' => 'ping'),
            array('sc_name' => 'storage')
        );
        $this->assertEquals($expectedResult, Servicecategory::getList('sc_name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('sc_id' => 1, 'sc_name' => 'ping'),
            array('sc_id' => 2, 'sc_name' => 'storage')
        );
        $this->assertEquals($expectedResult, Servicecategory::getList(array('sc_id', 'sc_name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('sc_name' => 'storage'),
            array('sc_name' => 'ping')
        );
        $this->assertEquals($expectedResult, Servicecategory::getList('sc_name', null, null, 'sc_id', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('sc_name' => 'ping')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicecategory::getList(
                'sc_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'sc_name' => 'ping'
                )
            )
        );
    }

    public function testGetListWithMultipleFilters()
    {
        $expectedResult = array(
            array('sc_name' => 'ping')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicecategory::getList(
                'sc_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'sc_name' => 'ping',
                    'sc_id' => 1
                )
            )
        );
    }

    public function testGetListWithFilterNoResult()
    {
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult, 
            Servicecategory::getList(
                'sc_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'sc_name' => 'idontexist',
                )
            )

        );
    }

    public function testGetListBySearch()
    {
        $expectedResult = array(
            array('sc_name' => 'storage')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicecategory::getListBySearch(
                'sc_name', 
                null, 
                null, 
                null, 
                null,
                array('sc_comment' => 'storage')
            )
        );
    }

    public function testGet()
    {
        $expectedResult = array(
            'sc_id' => 2,
            'sc_name' => 'storage',
            'sc_description' => 'storage',
            'level' => null,
            'icon_id' => null,
            'sc_comment' => 'Storage services',
            'sc_activate' => '1',
            'organization_id' => 1
        );
        $this->assertEquals($expectedResult, Servicecategory::get(2));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'sc_name' => 'ping'
        );
        $this->assertEquals($expectedResult, Servicecategory::get(1, 'sc_name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'sc_name' => 'ping',
            'sc_comment' => 'Ping services'
        );
        $this->assertEquals($expectedResult, Servicecategory::get(1, array('sc_name', 'sc_comment')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicecategory::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array(2);
        $this->assertEquals($expectedResult, Servicecategory::getIdByParameter('sc_name', 'storage'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array(1, 2);
        $this->assertEquals($expectedResult, Servicecategory::getIdByParameter('sc_name', array('ping', 'storage')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Servicecategory::getIdByParameter('idontexist', array('ping'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('sc_id', Servicecategory::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('sc_name', Servicecategory::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_servicecategories', Servicecategory::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'sc_id',
                'sc_name',
                'sc_description',
                'level',
                'icon_id',
                'sc_comment',
                'sc_activate',
                'organization_id'
            ),
            Servicecategory::getColumns()
        );
    }
}
