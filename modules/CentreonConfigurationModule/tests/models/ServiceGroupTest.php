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
use CentreonConfiguration\Models\Servicegroup;

class ServiceGroupTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $insert = array(
            'sg_name' => 'test',
            'sg_alias' => 'test alias',
            'sg_comment' => 'test comment',
            'sg_activate' => '1',
            'organization_id' => 1
        );
        Servicegroup::insert($insert);
        $this->tableEqualsXml(
            'cfg_servicegroups',
            dirname(__DIR__) . '/data/servicegroup.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $insert = array(
            'sg_name' => 'test',
            'sg_alias' => 'test alias',
            'sg_comment' => 'test comment',
            'sg_activate' => '1',
            'organization_id' => 1
        );
        Servicegroup::insert($insert);
        $this->setExpectedException(
            'PDOException'
        );
        Servicegroup::insert($insert);
    }

    public function testDelete()
    {
        Servicegroup::delete(2);
        $this->tableEqualsXml(
            'cfg_servicegroups',
            dirname(__DIR__) . '/data/servicegroup.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'sg_alias' => 'new_alias'
        );
        Servicegroup::update(2, $newInfo);
        $this->tableEqualsXml(
            'cfg_servicegroups',
            dirname(__DIR__) . '/data/servicegroup.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'sg_name' => 'france'
        );
        $this->setExpectedException(
            'PDOException'
        );
        Servicegroup::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
      $newInfo = array(
            'sg_name' => 'test'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        Servicegroup::duplicate(1);
        $this->tableEqualsXml(
            'cfg_servicegroups',
            dirname(__DIR__) . '/data/servicegroup.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        Servicegroup::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_servicegroups',
            dirname(__DIR__) . '/data/servicegroup.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::duplicate(9999, 2);
    }

    public function testGetAllParameters()
    {
        $testInformation = array(
            'sg_id' => 2,
            'sg_name' => 'england',
            'sg_alias' => 'england',
            'sg_comment' => 'English services',
            'sg_activate' => '1',
            'organization_id' => 1
        );
        $arr = Servicegroup::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'sg_name' => 'england',
        );
        $arr = Servicegroup::getParameters(2, 'sg_name');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'sg_name' => 'england',
            'sg_alias' => 'england',
            'sg_activate' => '1'
        );
        $arr = Servicegroup::getParameters(2, array('sg_name', 'sg_alias', 'sg_activate'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::getParameters(9999, 'sg_name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::getParameters(9999, array('sg_name', 'sg_alias'));
    }

    public function testGetUnknownParameters()
    {
       $this->setExpectedException(
           'PDOException'
       );
       Servicegroup::getParameters(2, 'idontexist');

       $this->setExpectedException(
           'PDOException'
       );
       Servicegroup::getParameters(2, array('sg_name', 'idontexist'));
    }

    public function testGetList()
    {
        $expectedResult = array(
            array(
                'sg_id' => 1,
                'sg_name' => 'france',
                'sg_alias' => 'france',
                'sg_comment' => 'French services',
                'sg_activate' => '1',
                'organization_id' => 1
            ),
            array(
                'sg_id' => 2,
                'sg_name' => 'england',
                'sg_alias' => 'england',
                'sg_comment' => 'English services',
                'sg_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Servicegroup::getList());
    }

    public function testGetListLimitOne()
    {
        $expectedResult = array(
            array(
                'sg_id' => 1,
                'sg_name' => 'france',
                'sg_alias' => 'france',
                'sg_comment' => 'French services',
                'sg_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Servicegroup::getList('*', 1));
    }

    public function testGetListSecondElementOnly()
    {
        $expectedResult = array(
            array(
                'sg_id' => 2,
                'sg_name' => 'england',
                'sg_alias' => 'england',
                'sg_comment' => 'English services',
                'sg_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Servicegroup::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('sg_name' => 'england'),
            array('sg_name' => 'france')
        );
        $this->assertEquals($expectedResult, Servicegroup::getList('sg_name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('sg_id' => 2, 'sg_name' => 'england'),
            array('sg_id' => 1, 'sg_name' => 'france')
        );
        $this->assertEquals($expectedResult, Servicegroup::getList(array('sg_id', 'sg_name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('sg_name' => 'england'),
            array('sg_name' => 'france')
        );
        $this->assertEquals($expectedResult, Servicegroup::getList('sg_name', null, null, 'sg_id', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('sg_name' => 'england')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicegroup::getList(
                'sg_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'sg_name' => 'england'
                )
            )
        );
    }

    public function testGetListWithMultipleFilters()
    {
        $expectedResult = array(
            array('sg_name' => 'france')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicegroup::getList(
                'sg_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'sg_name' => 'france',
                    'sg_id' => 1
                )
            )
        );
    }

    public function testGetListWithFilterNoResult()
    {
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult, 
            Servicegroup::getList(
                'sg_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'sg_name' => 'idontexist',
                )
            )

        );
    }

    public function testGetListBySearch()
    {
        $expectedResult = array(
            array('sg_name' => 'france')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicegroup::getListBySearch(
                'sg_name', 
                null, 
                null, 
                null, 
                null,
                array('sg_comment' => 'French')
            )
        );
    }

    public function testGet()
    {
        $expectedResult = array(
            'sg_id' => 1,
            'sg_name' => 'france',
            'sg_alias' => 'france',
            'sg_comment' => 'French services',
            'sg_activate' => '1',
            'organization_id' => 1
        );
        $this->assertEquals($expectedResult, Servicegroup::get(1));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'sg_name' => 'england'
        );
        $this->assertEquals($expectedResult, Servicegroup::get(2, 'sg_name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'sg_name' => 'france',
            'sg_comment' => 'French services'
        );
        $this->assertEquals($expectedResult, Servicegroup::get(1, array('sg_name', 'sg_comment')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array(2);
        $this->assertEquals($expectedResult, Servicegroup::getIdByParameter('sg_name', 'england'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array(2, 1);
        $this->assertEquals($expectedResult, Servicegroup::getIdByParameter('sg_name', array('england', 'france')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Servicegroup::getIdByParameter('idontexist', array('france'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('sg_id', Servicegroup::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('sg_name', Servicegroup::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_servicegroups', Servicegroup::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'sg_id',
                'sg_name',
                'sg_alias',
                'sg_comment',
                'sg_activate',
                'organization_id'
            ),
            Servicegroup::getColumns()
        );
    }
}
