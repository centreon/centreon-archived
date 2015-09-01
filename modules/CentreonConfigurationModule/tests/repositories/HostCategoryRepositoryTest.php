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

namespace Test\CentreonConfiguration\Repository;

require_once 'modules/CentreonConfigurationModule/tests/repositories/RepositoryTestCase.php';

use \Test\CentreonConfiguration\Repository\RepositoryTestCase;
use CentreonConfiguration\Repository\HostCategoryRepository;

class HostCategoryRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'hostcategory';
    protected $objectClass = '\CentreonConfiguration\Models\Hostcategory';
    protected $relationMap = array(
        'hc_hosts' => '\CentreonConfiguration\Models\Relation\Host\Hostcategory',
        'hc_hosttemplates' => '\CentreonConfiguration\Models\Relation\Hosttemplate\Hostcategory'
    ); 
    protected $repository = '\CentreonConfiguration\Repository\HostcategoryRepository';

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetFormListWithEmptySearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'network'),
            array('id' => 2, 'text' => 'web')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 2, 'text' => 'web')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('we'));
    }

    public function testGetFormListWithSearchStringWithNoResult()
    {
        $rep = $this->repository;
        $expectedResult = array();
        $this->assertEquals($expectedResult, $rep::getFormList('idontexist'));
    }

    public function testCreate()
    {
        $rep = $this->repository;
        $expectedResult = array();
        $rep::create(
            array(
                'hc_name' => 'test',
                'hc_alias' => 'test alias',
                'hc_comment' => 'test comment',
                'hc_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->tableEqualsXml(
            'cfg_hostcategories',
            dirname(__DIR__) . '/data/hostcategory.insert.xml'
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            'object_id' => 1,
            'hc_alias' => 'new alias'
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'cfg_hostcategories',
            dirname(__DIR__) . '/data/hostcategory.update.xml'
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(2));
        $this->tableEqualsXml(
            'cfg_hostcategories',
            dirname(__DIR__) . '/data/hostcategory.delete.xml'
        );
    }

    public function testDuplicate()
    {
        $rep = $this->repository;
        $rep::duplicate(
            array(1 => 2)
        );
        $this->tableEqualsXml(
            'cfg_hostcategories',
            dirname(__DIR__) . '/data/hostcategory.duplicate-2.xml'
        );
    }

    public function testGetRelations()
    {
        $rep = $this->repository;
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult,
            $rep::getRelations($this->relationMap['hc_hosts'], 1)
        );
    }
}
