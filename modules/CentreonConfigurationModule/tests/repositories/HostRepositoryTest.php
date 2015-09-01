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
use CentreonConfiguration\Repository\HostRepository;

class HostRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'host';
    protected $objectClass = '\CentreonConfiguration\Models\Host';
    protected $repository = '\CentreonConfiguration\Repository\HostRepository';

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetFormListWithEmptySearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 2, 'text' => 'Host 1'),
            array('id' => 3, 'text' => 'Host 2')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 3, 'text' => 'Host 2')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('2'));
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
                'host_template_model_htm_id' => 1,
                'host_name' => 'Test host',
                'host_alias' => 'Test host',
                'host_address' => '192.168.1.3',
                'display_name' => 'Test host',
                'host_comment' => 'Testing host',
                'organization_id' => 1,
                'environment_id' => null,
                'poller_id' => 1
            )
        );
        $this->tableEqualsXml(
            'cfg_hosts',
            dirname(__DIR__) . '/data/host.insert.xml'
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            'object_id' => 3,
            'host_comment' => 'Modified host',
            'host_activate' => '0'
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'cfg_hosts',
            dirname(__DIR__) . '/data/host.update.xml'
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(3));
        $this->tableEqualsXml(
            'cfg_hosts',
            dirname(__DIR__) . '/data/host.delete.xml'
        );
    }

    public function testDuplicate()
    {
        $rep = $this->repository;
        $rep::duplicate(
            array(2 => 1, 3 => 2)
        );
        $this->tableEqualsXml(
            'cfg_hosts',
            dirname(__DIR__) . '/data/host.duplicate-2.xml'
        );
    }
}
