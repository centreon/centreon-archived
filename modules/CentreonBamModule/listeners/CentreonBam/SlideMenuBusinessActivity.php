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
 */

namespace CentreonBam\Listeners\CentreonBam;
use Centreon\Internal\Di;
use CentreonMain\Events\SlideMenu as SlideMenuEvent;

class SlideMenuBusinessActivity 
{
    public static function execute(SlideMenuEvent $event)
    {   
        $router = Di::getDefault()->get('router');

        try {            
            $event->setDefaultMenu(
                array(
                    'name' => 'business_activity',
                    'url' => $router->getPathFor('/centreon-bam/business-activity/snapshotslide/') . $event->getId(),
                    'icon' => '',
                    'order' => 0,
                    'tpl' => "/viewtpl/CentreonBamModule/ba_slide"
                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'tag',
                    'url' => $router->getPathFor('/centreon-bam/business-activity/'.  $event->getId() . '/tags') ,
                    'icon' => '',
                    'order' => 1,
                    'tpl' => "/viewtpl/CentreonBamModule/tags_slide",
                    'default' => 1
                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'kpi',
                    'url' => $router->getPathFor('/centreon-bam/business-activity/' . $event->getId() . '/indicators'),
                    'icon' => '',
                    'order' => 2,
                    'tpl' => "/viewtpl/CentreonBamModule/indicators_slide"
                )
            );

        } catch (Exception $e) {

        }
    }
}
