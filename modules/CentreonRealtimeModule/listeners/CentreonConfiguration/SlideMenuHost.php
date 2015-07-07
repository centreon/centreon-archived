<?php

/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonMain\Listeners\CentreonMain;
use Centreon\Internal\Di;
use CentreonMain\Events\SlideMenu as SlideMenuEvent;

class SlideMenu 
{
    
    public static function execute(SlideMenuEvent $event)
    {
        
        $router = Di::getDefault()->get('router');
        try{
            
            $event->setDefaultMenu(
                array(
                    'name' => 'host',
                    'url' => $router->getPathFor('/centreon-configuration/host/snapshotslide/') . $event->getHostId(),
                    'icon' => '',
                    'order' => 0,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/host_slide"

                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'template',
                    'url' => $router->getPathFor('/centreon-configuration/hosttemplate/viewconfslide/') . $event->getHostId(),
                    'icon' => '',
                    'order' => 2,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/templates_slide"
                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'tag',
                    'url' => $router->getPathFor('/centreon-configuration/host/'.$event->getHostId().'/tags') ,
                    'icon' => '',
                    'order' => 1,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/tags_slide",
                    'default' => 1
                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'service',
                    'url' => $router->getPathFor('/centreon-configuration/host/'.$event->getHostId().'/service'),
                    'icon' => '',
                    'order' => 4,
                    'tpl' => "/viewtpl/CentreonConfigurationModule/services_slide"
                )
            );
            
            $event->addMenu(
                array(
                    'name' => 'incident',
                    'url' => $router->getPathFor('/centreon-realtime/host/'.$event->getHostId().'/issues'),
                    'icon' => '',
                    'order' => 5,
                    'tpl' => "/viewtpl/CentreonRealtimeModule/incidents_slide"
                )
            );
            
            
            
            
            
        }  catch (Exception $e) {

        }
        
       
        
    }
    
    
}
