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

namespace CentreonConfiguration\Listeners\CentreonConfiguration;
use Centreon\Internal\Di;
use CentreonMain\Events\SlideMenu as SlideMenuEvent;

class SlideMenuHostTemplate 
{
    
    public static function execute(SlideMenuEvent $event)
    {
        
        $router = Di::getDefault()->get('router');
        try{
            
            $event->setDefaultMenu(
                array(
                    'name' => 'host',
                    'url' => $router->getPathFor('/centreon-configuration/hosttemplate/snapshotslide/') . $event->getId(),
                    'icon' => '',
                    'order' => 0,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/host_slide"

                )
            );
            
            /*$event->addMenu(
                array(
                    'name' => 'template',
                    'url' => $router->getPathFor('/centreon-configuration/hosttemplate/viewconfslide/') . $event->getId(),
                    'icon' => '',
                    'order' => 2,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/templates_slide"
                )
            );*/
            
            $event->addMenu(
                array(
                    'name' => 'tag',
                    'url' => $router->getPathFor('/centreon-configuration/host/'.$event->getId().'/tags') ,
                    'icon' => '',
                    'order' => 1,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/tags_slide",
                    'default' => 1
                )
            );
            
            
            $event->addMenu(
                array(
                    'name' => 'command',
                    'url' => $router->getPathFor('/centreon-configuration/hosttemplate/'.$event->getId().'/command'),
                    'icon' => '',
                    'order' => 5,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/command_slide"
                )
            );

        }  catch (Exception $e) {

        }
    }
}

