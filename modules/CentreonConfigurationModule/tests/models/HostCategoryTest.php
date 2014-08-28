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
use \CentreonConfiguration\Models\Hostcategory;

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
            'hc_activate' => '1'
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
            'hc_activate' => '1'
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
            'hc_activate' => '1'
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
                'hc_activate' => '1'
            ),
            array(
                'hc_id' => 2,
                'hc_name' => 'web',
                'hc_alias' => 'web servers',
                'level' => null,
                'icon_id' => null,
                'hc_comment' => 'Web servers',
                'hc_activate' => '1'
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
                'hc_activate' => '1'
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
                'hc_activate' => '1'
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
            'hc_activate' => '1'
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
                'hc_activate'
            ),
            Hostcategory::getColumns()
        );
    }
}
