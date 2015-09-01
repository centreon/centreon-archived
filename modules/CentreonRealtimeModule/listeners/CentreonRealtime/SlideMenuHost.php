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

namespace CentreonRealtime\Listeners\CentreonRealtime;
use Centreon\Internal\Di;
use CentreonMain\Events\SlideMenu as SlideMenuEvent;

class SlideMenuHost 
{
    public static function execute(SlideMenuEvent $event)
    {   
        $router = Di::getDefault()->get('router');

        try {            
            $event->setDefaultMenu(
                array(
                    'name' => 'host',
                    'url' => $router->getPathFor('/centreon-realtime/host/snapshotslide/') . $event->getId(),
                    'icon' => '',
                    'order' => 0,
                    'tpl' => "/viewtpl/CentreonRealtimeModule/host_slide"

                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'command',
                    'url' => $router->getPathFor('/centreon-realtime/host/') . $event->getId() . '/command',
                    'icon' => '',
                    'order' => 1,
                    'tpl' => "/viewtpl/CentreonRealtimeModule/command_slide"
                )
            );

            $event->addMenu(
                array(
                    'name' => 'output',
                    'url' => $router->getPathFor('/centreon-realtime/host/') . $event->getId() . '/output',
                    'icon' => '',
                    'order' => 2,
                    'tpl' => "/viewtpl/CentreonRealtimeModule/output_slide"
                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'tag',
                    'url' => $router->getPathFor('/centreon-configuration/host/'.  $event->getId() . '/tags') ,
                    'icon' => '',
                    'order' => 3,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/tags_slide",
                    'default' => 1
                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'real-time',
                    'url' => $router->getPathFor('/centreon-realtime/host/' . $event->getId() . '/scheduling-infos'),
                    'icon' => '',
                    'order' => 4,
                    'tpl' => "/viewtpl/CentreonRealtimeModule/schedulinginfos_slide"
                )
            );

            $event->addMenu(
                array(
                    'name' => 'service',
                    'url' => $router->getPathFor('/centreon-realtime/host/' . $event->getId() . '/service'),
                    'icon' => '',
                    'order' => 5,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/services_slide"
                )
            );

        } catch (Exception $e) {

        }
    }
}
