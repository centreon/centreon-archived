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

use CentreonConfiguration\Events\BrokerProcess as BrokerProcessEvent;
use Centreon\Internal\Exception;

class BrokerProcess
{
    /**
     * @param \CentreonConfiguration\Events\BrokerProcess $event
     * @throws \Centreon\Internal\Exception
     */
    public static function execute(BrokerProcessEvent $event)
    {
        $action = $event->getAction();
        if (!in_array($action, array('reload', 'restart', 'forcereload'))) {
            throw new Exception(sprintf('Invalid action for Broker: %s', $action));
        }
        $command = "sudo /etc/init.d/cbd {$action} 2>&1";
        $status = 0;
        $output = array();
        exec($command, $output, $status);
        foreach ($output as $line) {
            $event->setOutput($line);
        }
        $event->setStatus(
            $status ? false : true
        );
    }
}
