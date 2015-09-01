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

namespace CentreonBroker\Listeners\CentreonMain;

use CentreonMain\Events\PostSave as PostSaveEvent;
use CentreonBroker\Repository\BrokerRepository;
use CentreonConfiguration\Models\Poller;

class PostSave
{
    /**
     * @param CentreonMain\Events\PostSave $event
     */
    public static function execute(PostSaveEvent $event)
    {
        $parameters = $event->getParameters();
        $extraParameters = $event->getExtraParameters();
        if ($event->getObjectName() === 'poller') {
            foreach ($parameters as $key => $value) {
                $extraParameters['centreon-broker'][$key] = $value;
            }
            if (($event->getAction() === 'update') && !isset($extraParameters['centreon-broker']['tmpl_name'])) {
                $templateName = Poller::getParameters($extraParameters['centreon-broker']['object_id'], 'tmpl_name');
                $extraParameters['centreon-broker']['tmpl_name'] = $templateName['tmpl_name'];
            }
            BrokerRepository::save($event->getObjectId(), $extraParameters['centreon-broker']);
        }
    }
}
