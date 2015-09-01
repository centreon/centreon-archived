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
use CentreonConfiguration\Events\SynchronizeFiles as SynchronizeFilesEvent;
use CentreonBroker\Repository\BrokerRepository;

class SynchronizeFiles
{
    /**
     * Execute action 
     *
     * @param \CentreonConfiguration\Events\SynchronizeFiles $event
     * @throws Exception
     */
    public static function execute(SynchronizeFilesEvent $event)
    {
        $endpoints = BrokerRepository::getConfigEndpoints($event->getPollerId());
        foreach ($endpoints as $endpoint) {
            try {
                BrokerRepository::sendCommand($event->getPollerId(), $endpoint . ';DUMP_DIR');
            } catch (\Exception $e) {
                $event->setOutput($e->getMessage());
            }
        }
    }
}
