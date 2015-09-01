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

namespace Test\CentreonBroker\Hooks;

use \Test\Centreon\DbTestCase;
use CentreonEngine\Hooks\DisplayBrokerPaths;

class DisplayBrokerPathsTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonBrokerModule/tests/data/json/';

    public function testExecuteWithoutNodeId()
    {
        $params = array();
        $result = DisplayBrokerPaths::execute($params);
        $this->assertArrayHasKey('template', $result);
        $this->assertArrayHasKey('variables', $result);
        $this->assertEquals('displayBrokerPaths.tpl', $result['template']);
        $this->assertArrayHasKey('paths', $result['variables']);
        $this->assertArrayHasKey('broker_module_directory', $result['variables']['paths']);
        $this->assertEquals('', $result['variables']['paths']['broker_module_directory']['value']);
    }

    public function testExecuteWithNodeId()
    {
        $this->markTestIncomplete("Must finish this test");
        /*$params = array('nodeId' => 1);
        $result = DisplayBrokerPaths::execute($params);
        $this->assertArrayHasKey('template', $result);
        $this->assertArrayHasKey('variables', $result);
        $this->assertEquals('displayBrokerPaths.tpl', $result['template']);
        $this->assertArrayHasKey('paths', $result['variables']);
        $this->assertArrayHasKey('broker_module_directory', $result['variables']['paths']);
        $this->assertEquals('', $result['variables']['paths']['broker_module_directory']['value']);*/

    }
}
