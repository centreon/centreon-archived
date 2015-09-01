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

use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use CentreonConfiguration\Events\UpdateDatabase as UpdateDatabaseEvent;
use CentreonBroker\Repository\BrokerRepository;

class UpdateDatabase
{
    /**
     * Execute action 
     *
     * @param \CentreonConfiguration\Events\UpdateDatabase $event
     * @throws Exception
     */
    public static function execute(UpdateDatabaseEvent $event)
    {
        $command = sprintf("[%u] UPDATE_CFG_DB;", time());
        
        try {
            BrokerRepository::sendCommand($command . $event->getPollerId(). "\n");
        } catch (\Exception $e) {
            $event->setOutput($e->getMessage());
        }
    }
}
