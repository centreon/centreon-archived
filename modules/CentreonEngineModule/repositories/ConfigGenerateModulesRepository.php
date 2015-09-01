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

namespace CentreonEngine\Repository;

use Centreon\Internal\Di;
use CentreonEngine\Models\Engine;
use CentreonConfiguration\Events\BrokerModule as BrokerModuleEvent;
use CentreonConfiguration\Internal\Poller\WriteConfigFile;

/**
 * @author Julien Mathis <jmathis@centreon.com>
 * @version 3.0.0
 */
class ConfigGenerateModulesRepository
{
    /** 
     * Generate modules configuration files
     * @param array $filesList
     * @param int $pollerId
     * @param string $path
     * @param object $event
     * @return value
     */
    public function generate(& $filesList, $pollerId, $path, $event)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        $modules = array();

        /* Retrieve broker modules */
        $events = Di::getDefault()->get('events');
        $moduleEvent = new BrokerModuleEvent($pollerId);
        $events->emit('centreon-configuration.broker.module', array($moduleEvent));
        $brokerModules = $moduleEvent->getModules();
        foreach ($brokerModules as $brokerModule) {
            $modules[]['broker_module'] = $brokerModule;
        }

        /* External command module */
        $moduleDir = Engine::getParameters($pollerId, 'module_dir');
        $modules[]['broker_module'] = rtrim($moduleDir['module_dir'], '/') . '/externalcmd.so';

        /* Write modules configuration files */
        foreach ($modules as $module) {
            $filename = preg_match('/\/?(\w+)\.so/', $module['broker_module'], $matches);
            if (!empty($matches[1])) {
                WriteConfigFile::writeParamsFile($module, $path . $pollerId . "/conf.d/" . $matches[1] . '.cfg', $filesList, $user = "API");
            }
        }
    }
}
