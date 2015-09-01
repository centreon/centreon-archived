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
use CentreonConfiguration\Models\Hostcategory;

class HostCategoryTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $insert = array(
            'hc_name' => 'test',
            'hc_alias' => 'test alias',
            'hc_comment' => 'test comment',
            'hc_activate' => '1',
            'organization_id' => 1
        );
        Hostcategory::insert($insert);
        $this->tableEqualsXml(
            'cfg_hostcategories',
            dirname(__DIR__) . '/data/hostcategory.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $insert = array(
            'hc_name' => 'test',
            'hc_alias' => 'test alias',
            'hc_comment' => 'test comment',
            'hc_activate' => '1',
            'organization_id' => 1
        );
        Hostcategory::insert($insert);
        $this->setExpectedException(
            'PDOException'
        );
        Hostcategory::insert($insert);
    }

    public function testDelete()
    {
        Hostcategory::delete(2);
        $this->tableEqualsXml(
            'cfg_hostcategories',
            dirname(__DIR__) . '/data/hostcategory.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostcategory::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'hc_alias' => 'new alias'
        );
        Hostcategory::update(1, $newInfo);
        $this->tableEqualsXml(
            'cfg_hostcategories',
            dirname(__DIR__) . '/data/hostcategory.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'hc_name' => 'network'
        );
        $this->setExpectedException(
            'PDOException'
        );
        Hostcategory::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
      $newInfo = array(
            'hc_name' => 'test'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostcategory::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        Hostcategory::duplicate(1);
        $this->tableEqualsXml(
            'cfg_hostcategories',
            dirname(__DIR__) . '/data/hostcategory.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        Hostcategory::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_hostcategories',
            dirname(__DIR__) . '/data/hostcategory.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostcategory::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostcategory::duplicate(9999, 2);
    }

    public function testGetAllParameters()
    {
        $testInformation = array(
            'hc_id' => 2,
            'hc_name' => 'web',
            'hc_alias' => 'web servers',
            'level' => null,
            'icon_id' => null,
            'hc_comment' => 'Web servers',
            'hc_activate' => '1',
            'organization_id' => 1
        );
        $arr = Hostcategory::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'hc_name' => 'web',
        );
        $arr = Hostcategory::getParameters(2, 'hc_name');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'hc_name' => 'web',
            'hc_alias' => 'web servers',
            'hc_activate' => '1'
        );
        $arr = Hostcategory::getParameters(2, array('hc_name', 'hc_alias', 'hc_activate'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostcategory::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostcategory::getParameters(9999, 'hc_name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostcategory::getParameters(9999, array('hc_name', 'hc_alias'));
    }

    public function testGetUnknownParameters()
    {
       $this->setExpectedException(
           'PDOException'
       );
       Hostcategory::getParameters(2, 'idontexist');

       $this->setExpectedException(
           'PDOException'
       );
       Hostcategory::getParameters(2, array('hc_name', 'idontexist'));
    }

    public function testGetList()
    {
        $expectedResult = array(
            array(
                'hc_id' => 1,
                'hc_name' => 'network',
                'hc_alias' => 'network devices',
                'level' => null,
                'icon_id' => null,
                'hc_comment' => 'Network devices',
                'hc_activate' => '1',
                'organization_id' => 1
            ),
            array(
                'hc_id' => 2,
                'hc_name' => 'web',
                'hc_alias' => 'web servers',
                'level' => null,
                'icon_id' => null,
                'hc_comment' => 'Web servers',
                'hc_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Hostcategory::getList());
    }

    public function testGetListLimitOne()
    {
        $expectedResult = array(
            array(
                'hc_id' => 1,
                'hc_name' => 'network',
                'hc_alias' => 'network devices',
                'level' => null,
                'icon_id' => null,
                'hc_comment' => 'Network devices',
                'hc_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Hostcategory::getList('*', 1));
    }

    public function testGetListSecondElementOnly()
    {
        $expectedResult = array(
            array(
                'hc_id' => 2,
                'hc_name' => 'web',
                'hc_alias' => 'web servers',
                'level' => null,
                'icon_id' => null,
                'hc_comment' => 'Web servers',
                'hc_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Hostcategory::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('hc_name' => 'network'),
            array('hc_name' => 'web')
        );
        $this->assertEquals($expectedResult, Hostcategory::getList('hc_name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('hc_id' => 1, 'hc_name' => 'network'),
            array('hc_id' => 2, 'hc_name' => 'web')
        );
        $this->assertEquals($expectedResult, Hostcategory::getList(array('hc_id', 'hc_name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('hc_name' => 'web'),
            array('hc_name' => 'network')
        );
        $this->assertEquals($expectedResult, Hostcategory::getList('hc_name', null, null, 'hc_id', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('hc_name' => 'web')
        );
        $this->assertEquals(
            $expectedResult, 
            Hostcategory::getList(
                'hc_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'hc_name' => 'web'
                )
            )
        );
    }

    public function testGetListWithMultipleFilters()
    {
        $expectedResult = array(
            array('hc_name' => 'web')
        );
        $this->assertEquals(
            $expectedResult, 
            Hostcategory::getList(
                'hc_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'hc_name' => 'web',
                    'hc_id' => 2
                )
            )
        );
    }

    public function testGetListWithFilterNoResult()
    {
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult, 
            Hostcategory::getList(
                'hc_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'hc_name' => 'idontexist',
                )
            )

        );
    }

    public function testGetListBySearch()
    {
        $expectedResult = array(
            array('hc_name' => 'web')
        );
        $this->assertEquals(
            $expectedResult, 
            Hostcategory::getListBySearch(
                'hc_name', 
                null, 
                null, 
                null, 
                null,
                array('hc_alias' => 'servers')
            )
        );
    }

    public function testGet()
    {
        $expectedResult = array(
            'hc_id' => 2,
            'hc_name' => 'web',
            'hc_alias' => 'web servers',
            'level' => null,
            'icon_id' => null,
            'hc_comment' => 'Web servers',
            'hc_activate' => '1',
            'organization_id' => 1
        );
        $this->assertEquals($expectedResult, Hostcategory::get(2));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'hc_name' => 'network'
        );
        $this->assertEquals($expectedResult, Hostcategory::get(1, 'hc_name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'hc_name' => 'network',
            'hc_comment' => 'Network devices'
        );
        $this->assertEquals($expectedResult, Hostcategory::get(1, array('hc_name', 'hc_comment')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostcategory::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array(2);
        $this->assertEquals($expectedResult, Hostcategory::getIdByParameter('hc_name', 'web'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array(1, 2);
        $this->assertEquals($expectedResult, Hostcategory::getIdByParameter('hc_name', array('web', 'network')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Hostcategory::getIdByParameter('idontexist', array('web'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('hc_id', Hostcategory::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('hc_name', Hostcategory::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_hostcategories', Hostcategory::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'hc_id',
                'hc_name',
                'hc_alias',
                'level',
                'icon_id',
                'hc_comment',
                'hc_activate',
                'organization_id'
            ),
            Hostcategory::getColumns()
        );
    }
}
