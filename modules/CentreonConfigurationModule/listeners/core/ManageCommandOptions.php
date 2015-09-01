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

namespace CentreonConfiguration\Listeners\Core;

use Centreon\Events\ManageCommandOptions as ManageCommandOptionsEvent;
use CentreonConfiguration\Repository\PollerRepository;
use CentreonConfiguration\Models\Poller;

class ManageCommandOptions
{
    /**
     * @param Core\Events\ManageCommandOptions $event
     */
    public static function execute(ManageCommandOptionsEvent $event)
    {
        $options = $event->getOptions();
        $args = $event->getArgs();
        $newInfos = array();
        if ($event->getObjectName() == 'poller') {
            if (($event->getAction() == 'createAction') && isset($args['template'])) {
                $newInfos = PollerRepository::addCommandTemplateInfos($args['template']);
            } else if (($event->getAction() == 'updateAction') && isset($args['poller'])) {
                $pollerIds = Poller::getIdByParameter('slug', array($args['poller']));
                if (isset($pollerIds[0])) {
                    $pollerId = $pollerIds[0];
                    $templateName = Poller::getParameters($pollerId, 'tmpl_name');
                    $newInfos = PollerRepository::addCommandTemplateInfos($templateName['tmpl_name']);
                }
            }
        }

        foreach ($newInfos as $newInfo) {
            $newOption = array(
                $newInfo['name'] => array(
                    'paramType' => 'params',
                    'help' => '',
                    'type' => 'string',
                    'toTransform' => $newInfo['name'],
                    'multiple' => '',
                    'required' => '0',
                )
            );
            if (isset($newInfo['help'])) {
                $newOption[$newInfo['name']]['help'] = $newInfo['help'];
            }
            if (isset($newInfo['require']) && ($newInfo['require'] == true) && ($event->getAction() == 'createAction')) {
                $newOption[$newInfo['name']]['required'] = '1';
            }
            $event->addOption($newOption);
        }
    }
}
