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
