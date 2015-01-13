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
