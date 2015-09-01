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

class SlideMenuService
{
    
    public static function execute(SlideMenuEvent $event)
    {
        
        $router = Di::getDefault()->get('router');
        try{
            
            $event->setDefaultMenu(
                array(
                    'name' => 'service',
                    'url' => $router->getPathFor('/centreon-realtime/service/snapshotslide/') . $event->getId(),
                    'icon' => '',
                    'order' => 0,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/service_slide"
                )
            );

            $event->addMenu(
                array(
                    'name' => 'incident',
                    'url' => $router->getPathFor('/centreon-realtime/service/incidentslide/') . $event->getId(),
                    'icon' => '',
                    'order' => 5,
                    'tpl' => "/viewtpl/CentreonRealtimeModule/incidents_slide"
                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'tag',
                    'url' => $router->getPathFor('/centreon-realtime/service/tagslide/') . $event->getId(),
                    'icon' => '',
                    'order' => 3,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/tags_slide"
                )
            );
            
            
            $event->addMenu(
                array(
                    'name' => 'command',
                    'url' => $router->getPathFor('/centreon-realtime/service/slidecommand/') . $event->getId(),
                    'icon' => '',
                    'order' => 1,
                    'tpl' => "/viewtpl/CentreonRealtimeModule/command_slide"
                )
            );
            $event->addMenu(
                array(
                    'name' => 'output',
                    'url' => $router->getPathFor('/centreon-realtime/service/slideoutput/') . $event->getId(),
                    'icon' => '',
                    'order' => 2,
                    'tpl' => "/viewtpl/CentreonRealtimeModule/output_slide"
                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'real-time',
                    'url' => $router->getPathFor('/centreon-realtime/service/slideschelduded/') . $event->getId(),
                    'icon' => '',
                    'order' => 4,
                    'tpl' => "/viewtpl/CentreonRealtimeModule/schedulinginfos_slide"
                )
            );
            
            
            
            
            
            
            
            

        }  catch (Exception $e) {

        }
        
       
        
    }
    
    
}
