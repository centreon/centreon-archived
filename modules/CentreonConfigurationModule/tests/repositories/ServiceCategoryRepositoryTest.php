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

class ServiceCategoryRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'servicecategory';
    protected $objectClass = '\CentreonConfiguration\Models\Servicecategory';
    protected $relationMap = array(
        'sc_services' => '\CentreonConfiguration\Models\Relation\Service\Servicecategory',
        'sc_servicetemplates' => '\CentreonConfiguration\Models\Relation\Servicetemplate\Servicecategory'
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
            array('id' => 1, 'text' => 'ping'),
            array('id' => 2, 'text' => 'storage')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 2, 'text' => 'storage')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('storage'));
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
                'sc_name' => 'test',
                'sc_description' => 'test description',
                'sc_comment' => 'test comment',
                'sc_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->tableEqualsXml(
            'cfg_servicecategories',
            dirname(__DIR__) . '/data/servicecategory.insert.xml'
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            'object_id' => 1,
            'sc_description' => 'new description'
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'cfg_servicecategories',
            dirname(__DIR__) . '/data/servicecategory.update.xml'
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(2));
        $this->tableEqualsXml(
            'cfg_servicecategories',
            dirname(__DIR__) . '/data/servicecategory.delete.xml'
        );
    }

    public function testDuplicate()
    {
        $rep = $this->repository;
        $rep::duplicate(
            array(1 => 2)
        );
        $this->tableEqualsXml(
            'cfg_servicecategories',
            dirname(__DIR__) . '/data/servicecategory.duplicate-2.xml'
        );
    }

    public function testGetRelations()
    {
        $rep = $this->repository;
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult,
            $rep::getRelations($this->relationMap['sc_services'], 1)
        );
    }
}
