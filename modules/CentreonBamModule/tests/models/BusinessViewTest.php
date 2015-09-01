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

namespace Test\CentreonBusinessView\Models;

use \Test\Centreon\DbTestCase;
use CentreonBam\Models\BusinessView;

class BusinessViewTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonBamModule/tests/data/json/';

    public function testInsert()
    {
        $newBusinessView = array(
            "ba_group_name" => "Business View Test",
            "ba_group_description" => "Business View Test",
            "visible" => '1',
            "organization_id" => 2,
        );
        BusinessView::insert($newBusinessView);
        $this->tableEqualsXml(
            'cfg_bam_bagroups',
            dirname(__DIR__) . '/data/businessview.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $newBusinessView = array(
            'ba_group_name' => 'accouting',
            'organization' => '1',
        );
        $this->setExpectedException(
            'PDOException'
        );
        BusinessView::insert($newBusinessView);
    }

    public function testDelete()
    {
        BusinessView::delete(1);
        $this->tableEqualsXml(
            'cfg_bam_bagroups',
            dirname(__DIR__) . '/data/businessview.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'ba_group_name' => 'modified business view',
            'visible' => '0',
        );
        BusinessView::update(2, $newInfo);
        $this->tableEqualsXml(
            'cfg_bam_bagroups',
            dirname(__DIR__) . '/data/businessview.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'ba_group_name' => 'view 1',
            "organization_id" => 1
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        BusinessView::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
      $newInfo = array(
            'organization_id' => '5'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        BusinessView::duplicate(1);
        $this->tableEqualsXml(
            'cfg_bam_bagroups',
            dirname(__DIR__) . '/data/businessview.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        BusinessView::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_bam_bagroups',
            dirname(__DIR__) . '/data/businessview.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::duplicate(9999, 2);
    }

    public function testGetAllParameters()
    {
        $testInformation = array(
            "id_ba_group" => "2",
            "ba_group_name" => "view 2",
            "ba_group_description" => "view 2",
            "visible" => '1',
            "organization_id" => 1,
        );
        $arr = BusinessView::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'ba_group_description' => 'view 2',
        );
        $arr = BusinessView::getParameters(2, 'ba_group_description');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'ba_group_name' => 'view 2',
            'ba_group_description' => 'view 2',
            'visible' => '1'
        );
        $arr = BusinessView::getParameters(2, array('ba_group_name', 'ba_group_description', 'visible'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::getParameters(9999, 'ba_group_name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::getParameters(9999, array('ba_group_name', 'ba_group_description'));
    }

    public function testGetUnknownParameters()
    {
       $this->setExpectedException(
           'PDOException'
       );
       BusinessView::getParameters(2, 'idontexist');

       $this->setExpectedException(
           'PDOException'
       );
       BusinessView::getParameters(2, array('ba_group_name', 'idontexist'));
    }

    public function testGetList()
    {
        $expectedResult = array(
            array(
                "id_ba_group" => "1",
                "ba_group_name" => "view 1",
                "ba_group_description" => "view 1",
                "visible" => '1',
                "organization_id" => 1,
            ),
            array(
                "id_ba_group" => "2",
                "ba_group_name" => "view 2",
                "ba_group_description" => "view 2",
                "visible" => '1',
                "organization_id" => 1,
            )
        );
        $this->assertEquals($expectedResult, BusinessView::getList());
    }

    public function testGetListLimitOne()
    {
        $expectedResult = array(
            array(
                "id_ba_group" => "1",
                "ba_group_name" => "view 1",
                "ba_group_description" => "view 1",
                "visible" => '1',
                "organization_id" => 1,
            )
        );
        $this->assertEquals($expectedResult, BusinessView::getList('*', 1));
    }

    public function testGetListSecondElementOnly()
    {
        $expectedResult = array(
            array(
                "id_ba_group" => "2",
                "ba_group_name" => "view 2",
                "ba_group_description" => "view 2",
                "visible" => '1',
                "organization_id" => 1,
            )
        );
        $this->assertEquals($expectedResult, BusinessView::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('ba_group_name' => 'view 1'),
            array('ba_group_name' => 'view 2')
        );
        $this->assertEquals($expectedResult, BusinessView::getList('ba_group_name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('id_ba_group' => 1, 'ba_group_name' => 'view 1'),
            array('id_ba_group' => 2, 'ba_group_name' => 'view 2')
        );
        $this->assertEquals($expectedResult, BusinessView::getList(array('id_ba_group', 'ba_group_name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('ba_group_name' => 'view 2'),
            array('ba_group_name' => 'view 1')
        );
        $this->assertEquals($expectedResult, BusinessView::getList('ba_group_name', null, null, 'id_ba_group', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('ba_group_name' => 'view 2')
        );
        $this->assertEquals(
            $expectedResult, 
            BusinessView::getList(
                'ba_group_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'ba_group_name' => 'view 2'
                )
            )
        );
    }

    public function testGetListWithMultipleFilters()
    {
        $expectedResult = array(
            array('ba_group_name' => 'view 1')
        );
        $this->assertEquals(
            $expectedResult, 
            BusinessView::getList(
                'ba_group_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'ba_group_name' => 'view 1',
                    'id_ba_group' => 1
                )
            )
        );
    }

    public function testGetListWithFilterNoResult()
    {
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult, 
            BusinessView::getList(
                'ba_group_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'ba_group_name' => 'idontexist',
                )
            )

        );
    }

    public function testGetListBySearch()
    {
        $expectedResult = array(
            array('ba_group_name' => 'view 1'),
            array('ba_group_name' => 'view 2')
        );
        $this->assertEquals(
            $expectedResult, 
            BusinessView::getListBySearch(
                'ba_group_name', 
                null, 
                null, 
                null, 
                null,
                array('ba_group_name' => 'view')
            )
        );
    }

    public function testGet()
    {
        $expectedResult = array(
            "id_ba_group" => "2",
            "ba_group_name" => "view 2",
            "ba_group_description" => "view 2",
            "visible" => '1',
            "organization_id" => 1,
        );
        $this->assertEquals($expectedResult, BusinessView::get(2));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'ba_group_name' => 'view 1'
        );
        $this->assertEquals($expectedResult, BusinessView::get(1, 'ba_group_name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'ba_group_name' => 'view 1',
            'visible' => 1
        );
        $this->assertEquals($expectedResult, BusinessView::get(1, array('ba_group_name', 'visible')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array(1);
        $this->assertEquals($expectedResult, BusinessView::getIdByParameter('ba_group_name', 'view 1'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array(1, 2);
        $this->assertEquals($expectedResult, BusinessView::getIdByParameter('ba_group_name', array('view 1', 'view 2')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        BusinessView::getIdByParameter('idontexist', array('hotline'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('id_ba_group', BusinessView::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('ba_group_name', BusinessView::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_bam_bagroups', BusinessView::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                "id_ba_group",
                "ba_group_name",
                "ba_group_description",
                "visible",
                "organization_id"
            ),
            BusinessView::getColumns()
        );
    }
}
