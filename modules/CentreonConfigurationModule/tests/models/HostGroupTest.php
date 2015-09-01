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
use CentreonConfiguration\Models\Hostgroup;

class HostGroupTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $insert = array(
            'hg_name' => 'test',
            'hg_alias' => 'test alias',
            'hg_comment' => 'test comment',
            'hg_activate' => '1',
            'organization_id' => 1
        );
        Hostgroup::insert($insert);
        $this->tableEqualsXml(
            'cfg_hostgroups',
            dirname(__DIR__) . '/data/hostgroup.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $insert = array(
            'hg_name' => 'test',
            'hg_alias' => 'test alias',
            'hg_comment' => 'test comment',
            'hg_activate' => '1',
            'organization_id' => 1
        );
        Hostgroup::insert($insert);
        $this->setExpectedException(
            'PDOException'
        );
        Hostgroup::insert($insert);
    }

    public function testDelete()
    {
        Hostgroup::delete(2);
        $this->tableEqualsXml(
            'cfg_hostgroups',
            dirname(__DIR__) . '/data/hostgroup.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostgroup::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'hg_alias' => 'new_alias'
        );
        Hostgroup::update(2, $newInfo);
        $this->tableEqualsXml(
            'cfg_hostgroups',
            dirname(__DIR__) . '/data/hostgroup.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'hg_name' => 'france'
        );
        $this->setExpectedException(
            'PDOException'
        );
        Hostgroup::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
      $newInfo = array(
            'hg_name' => 'test'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostgroup::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        Hostgroup::duplicate(1);
        $this->tableEqualsXml(
            'cfg_hostgroups',
            dirname(__DIR__) . '/data/hostgroup.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        Hostgroup::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_hostgroups',
            dirname(__DIR__) . '/data/hostgroup.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostgroup::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostgroup::duplicate(9999, 2);
    }

    public function testGetAllParameters()
    {
        $testInformation = array(
            'hg_id' => 2,
            'hg_name' => 'england',
            'hg_alias' => 'england',
            'hg_notes' => null,
            'hg_notes_url' => null,
            'hg_action_url' => null,
            'hg_icon_image' => null,
            'hg_map_icon_image' => null,
            'hg_rrd_retention' => null,
            'hg_comment' => 'English servers',
            'hg_activate' => '1',
            'organization_id' => 1
        );
        $arr = Hostgroup::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'hg_name' => 'england',
        );
        $arr = Hostgroup::getParameters(2, 'hg_name');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'hg_name' => 'england',
            'hg_alias' => 'england',
            'hg_activate' => '1'
        );
        $arr = Hostgroup::getParameters(2, array('hg_name', 'hg_alias', 'hg_activate'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostgroup::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostgroup::getParameters(9999, 'hg_name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostgroup::getParameters(9999, array('hg_name', 'hg_alias'));
    }

    public function testGetUnknownParameters()
    {
       $this->setExpectedException(
           'PDOException'
       );
       Hostgroup::getParameters(2, 'idontexist');

       $this->setExpectedException(
           'PDOException'
       );
       Hostgroup::getParameters(2, array('hg_name', 'idontexist'));
    }

    public function testGetList()
    {
        $expectedResult = array(
            array(
                'hg_id' => 1,
                'hg_name' => 'france',
                'hg_alias' => 'france',
                'hg_notes' => null,
                'hg_notes_url' => null,
                'hg_action_url' => null,
                'hg_icon_image' => null,
                'hg_map_icon_image' => null,
                'hg_rrd_retention' => null,
                'hg_comment' => 'French servers',
                'hg_activate' => '1',
                'organization_id' => 1
            ),
            array(
                'hg_id' => 2,
                'hg_name' => 'england',
                'hg_alias' => 'england',
                'hg_notes' => null,
                'hg_notes_url' => null,
                'hg_action_url' => null,
                'hg_icon_image' => null,
                'hg_map_icon_image' => null,
                'hg_rrd_retention' => null,
                'hg_comment' => 'English servers',
                'hg_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Hostgroup::getList());
    }

    public function testGetListLimitOne()
    {
        $expectedResult = array(
            array(
                'hg_id' => 1,
                'hg_name' => 'france',
                'hg_alias' => 'france',
                'hg_notes' => null,
                'hg_notes_url' => null,
                'hg_action_url' => null,
                'hg_icon_image' => null,
                'hg_map_icon_image' => null,
                'hg_rrd_retention' => null,
                'hg_comment' => 'French servers',
                'hg_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Hostgroup::getList('*', 1));
    }

    public function testGetListSecondElementOnly()
    {
        $expectedResult = array(
            array(
                'hg_id' => 2,
                'hg_name' => 'england',
                'hg_alias' => 'england',
                'hg_notes' => null,
                'hg_notes_url' => null,
                'hg_action_url' => null,
                'hg_icon_image' => null,
                'hg_map_icon_image' => null,
                'hg_rrd_retention' => null,
                'hg_comment' => 'English servers',
                'hg_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Hostgroup::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('hg_name' => 'england'),
            array('hg_name' => 'france')
        );
        $this->assertEquals($expectedResult, Hostgroup::getList('hg_name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('hg_id' => 2, 'hg_name' => 'england'),
            array('hg_id' => 1, 'hg_name' => 'france')
        );
        $this->assertEquals($expectedResult, Hostgroup::getList(array('hg_id', 'hg_name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('hg_name' => 'england'),
            array('hg_name' => 'france')
        );
        $this->assertEquals($expectedResult, Hostgroup::getList('hg_name', null, null, 'hg_id', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('hg_name' => 'england')
        );
        $this->assertEquals(
            $expectedResult, 
            Hostgroup::getList(
                'hg_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'hg_name' => 'england'
                )
            )
        );
    }

    public function testGetListWithMultipleFilters()
    {
        $expectedResult = array(
            array('hg_name' => 'france')
        );
        $this->assertEquals(
            $expectedResult, 
            Hostgroup::getList(
                'hg_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'hg_name' => 'france',
                    'hg_id' => 1
                )
            )
        );
    }

    public function testGetListWithFilterNoResult()
    {
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult, 
            Hostgroup::getList(
                'hg_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'hg_name' => 'idontexist',
                )
            )

        );
    }

    public function testGetListBySearch()
    {
        $expectedResult = array(
            array('hg_name' => 'france')
        );
        $this->assertEquals(
            $expectedResult, 
            Hostgroup::getListBySearch(
                'hg_name', 
                null, 
                null, 
                null, 
                null,
                array('hg_comment' => 'French')
            )
        );
    }

    public function testGet()
    {
        $expectedResult = array(
            'hg_id' => 1,
            'hg_name' => 'france',
            'hg_alias' => 'france',
            'hg_notes' => null,
            'hg_notes_url' => null,
            'hg_action_url' => null,
            'hg_icon_image' => null,
            'hg_map_icon_image' => null,
            'hg_rrd_retention' => null,
            'hg_comment' => 'French servers',
            'hg_activate' => '1',
            'organization_id' => 1
        );
        $this->assertEquals($expectedResult, Hostgroup::get(1));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'hg_name' => 'england'
        );
        $this->assertEquals($expectedResult, Hostgroup::get(2, 'hg_name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'hg_name' => 'france',
            'hg_comment' => 'French servers'
        );
        $this->assertEquals($expectedResult, Hostgroup::get(1, array('hg_name', 'hg_comment')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Hostgroup::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array(2);
        $this->assertEquals($expectedResult, Hostgroup::getIdByParameter('hg_name', 'england'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array(2, 1);
        $this->assertEquals($expectedResult, Hostgroup::getIdByParameter('hg_name', array('england', 'france')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Hostgroup::getIdByParameter('idontexist', array('france'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('hg_id', Hostgroup::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('hg_name', Hostgroup::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_hostgroups', Hostgroup::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'hg_id',
                'hg_name',
                'hg_alias',
                'hg_notes',
                'hg_notes_url',
                'hg_action_url',
                'hg_icon_image',
                'hg_map_icon_image',
                'hg_rrd_retention',
                'hg_comment',
                'hg_activate',
                'organization_id'
            ),
            Hostgroup::getColumns()
        );
    }
}
