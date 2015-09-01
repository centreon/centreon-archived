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

namespace CentreonBroker\Listeners\CentreonConfiguration;

use CentreonConfiguration\Events\BrokerModule as BrokerModuleEvent;
use Centreon\Internal\Exception;
use CentreonBroker\Repository\BrokerRepository;

class BrokerModule
{
    /**
     * @param \CentreonConfiguration\Events\BrokerModule $event
     * @throws \Centreon\Internal\Exception
     */
    public static function execute(BrokerModuleEvent $event)
    {
        /* Retrieve etc and module path */
        $paths = BrokerRepository::getPathsFromPollerId($event->getPollerId());
        
        /* Set modules */
        if (isset($paths['directory_cbmod']) && isset($paths['directory_config'])) {
            $moduleDir = rtrim($paths['directory_cbmod'], '/');
            $etcDir = rtrim($paths['directory_config'], '/');
            $event->addModule("{$moduleDir}/cbmod.so {$etcDir}/poller-module.xml");
        }
    }
}
