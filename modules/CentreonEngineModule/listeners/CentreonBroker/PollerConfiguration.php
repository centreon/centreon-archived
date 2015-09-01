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

namespace CentreonEngine\Listeners\CentreonBroker;

use CentreonEngine\Models\Engine;
use CentreonMain\Events\Generic as GenericEvent;

/**
 * Listeners for event
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonBroker
 */
class PollerConfiguration
{

    /**
     * Execute the event
     *
     * @param \CentreonMain\Events\Generic $event The object for event
     */
    public static function execute(GenericEvent $event)
    {
        $input = $event->getInput();
        if (false === isset($input['poller_id'])) {
            throw new \InvalidArgumentException();
        }
        
        $pollerInformation = Engine::get($input['poller_id']);
        $delimiter = '';
        if (isset($input['delimiter'])) {
            $delimiter = $input['delimiter'];
        }
        $keys = array_map(
            function ($name) use ($delimiter) {
                return $delimiter . 'engine_' . $name . $delimiter;
            },
            array_keys($pollerInformation)
        );
        $values = array_combine($keys, array_values($pollerInformation));
        $event->setOutput($values);
    }
}
