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
 */

namespace Centreon\Commands\Module;

use Centreon\Internal\Module\Informations;
use Centreon\Internal\Command\AbstractCommand;

/**
 * 
 */
class InfosCommand extends AbstractCommand
{
    
    public $options = array(
        "simpleListAction" => array(
            "onlyActivated" => array(
                "functionParams" => "onlyActivated",
                "help" => "",
                "type" => "number",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            )
        ),
        "extendedListAction" => array(
            "onlyActivated" => array(
                "functionParams" => "onlyActivated",
                "help" => "",
                "type" => "number",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            ),
            "header" => array(
                "functionParams" => "header",
                "help" => "",
                "type" => "number",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            )
        )
    );

    /**
     * List module names
     * @param string $type
     * @param string $status
     */
    public function simpleListAction($onlyActivated = 1)
    {
        $moduleList = Informations::getModuleList($onlyActivated);
        foreach ($moduleList as $module) {
            echo $module."\n";
        }
    }

    /**
     * List all information about modules
     * @param string $type
     * @param int $onlyActivated
     * @param int $header
     */
    public function extendedListAction($onlyActivated = 1, $header = 1)
    {
        $moduleList = Informations::getModuleExtendedList($onlyActivated);
        if ($header) {
            echo 'name;alias;description;version;author;isactivated;isinstalled' . "\n";
        }
        foreach ($moduleList as $module) {
            echo $module['name'].';'.
                $module['alias'].';'.
                $module['description'].';'.
                $module['version'].';'.
                $module['author'].';'.
                $module['isactivated'].';'.
                $module['isinstalled']."\n";
        }
    }

    /**
     * 
     * @param string $moduleName
     */
    public function showAction($moduleName)
    {
        
    }
}
