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

namespace Test\CentreonEngine\Repository;

use Test\Centreon\DbTestCase;
use Centreon\Internal\Di;
use Centreon\Internal\Utils\Filesystem\Directory;
use CentreonEngine\Repository\ServiceRepository;
use CentreonEngine\Events\GetMacroService as ServiceMacroEvent;

class ServiceRepositoryTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonEngineModule/tests/data/json/';
    protected $tmpDir;

    public function testGenerate()
    {
        $resultContent = array(
            array(
                "type" => "service",
                "content" => array(
                    "_SERVICE_ID" => "2",
                    "host_name" => "host1",
                    "service_description" => "service1",
                    "alias" => "Service 1",
                    "command_command_id_arg" => "90",
                    "check_command" => "Check command 190",
                    "check_period" => "all",
                    "display_name" => "Service 1",
                    "check_interval" => "10",
                    "retry_interval" => "10",
                    "initial_state" => "u"
                )
            )
        );

        $serviceMacroEvent = new ServiceMacroEvent(2);
        $content = ServiceRepository::generate(2, $serviceMacroEvent);
        $this->assertEquals($resultContent, $content);
        $resultContent = array(
            array(
                "type" => "service",
                "content" => array(
                    "_SERVICE_ID" => "3",
                    "host_name" => "host2",
                    "service_description" => "service2",
                    "display_name" => "Service 2",
                    "alias" => "Service 2",
                    "use" => "servicetemplate1"
                )
            )
        );

        $serviceMacroEvent = new ServiceMacroEvent(3);
        $content = ServiceRepository::generate(3, $serviceMacroEvent);
        $this->assertEquals($resultContent, $content);
    }
}
