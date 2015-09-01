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
use CentreonConfiguration\Repository\ServiceTemplateRepository;

class ServiceTemplateRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'service';
    protected $objectClass = '\CentreonConfiguration\Models\Servicetemplate';
    protected $repository = '\CentreonConfiguration\Repository\ServicetemplateRepository';

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetFormListWithEmptySearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'ping-template'),
            array('id' => 2, 'text' => 'load-template')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 2, 'text' => 'load-template')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('load'));
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
                'service_description' => 'test',
                'service_alias' => 'test alias',
                'service_template_model_stm_id' => '1',
                'service_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->tableEqualsXml(
            'cfg_services',
            dirname(__DIR__) . '/data/servicetemplate.insert.xml'
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            'object_id' => 1,
            'service_alias' => 'new_alias'
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'cfg_services',
            dirname(__DIR__) . '/data/servicetemplate.update.xml'
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(2));
        $this->tableEqualsXml(
            'cfg_services',
            dirname(__DIR__) . '/data/servicetemplate.delete.xml'
        );
    }

    public function testDuplicate()
    {
        $rep = $this->repository;
        $rep::duplicate(
            array(1 => 2)
        );
        $this->tableEqualsXml(
            'cfg_services',
            dirname(__DIR__) . '/data/servicetemplate.duplicate-2.xml'
        );
    }
}
