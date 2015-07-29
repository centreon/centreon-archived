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

namespace CentreonRealtime\Listeners\CentreonMain;

use CentreonMain\Events\Status as StatusEvent;
use Centreon\Internal\Di;
use CentreonRealtime\Repository\HostRepository;
use CentreonRealtime\Repository\ServiceRepository;
use CentreonRealtime\Repository\IncidentsRepository;
use Centreon\Internal\Utils\Datetime;
use CentreonRealtime\Repository\PollerRepository;
use CentreonConfiguration\Repository\HostRepository as HostRepositoryConfig;
use CentreonConfiguration\Repository\ServiceRepository as ServiceRepositoryConfig;


/**
 * Event to top counter for host and service
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonMain
 */
class Status
{
    /**
     * Execute the event
     *
     * @param \CentreonMain\Events\Status $event The event object
     */
    public static function execute(StatusEvent $event)
    {
        
        $router = Di::getDefault()->get('router');
        $arrayStatusService = array('success','warning','critical','unknown','pending');
        $arrayStatusHost = array('success','critical','unreachable','pending');
        $totalHosts = 0;
        $totalServices = 0;
        $incidents = IncidentsRepository::getIncidents();
        $issues = array();
        foreach($incidents as $incident){
            if(is_null($incident['service_desc'])){
                $state = $arrayStatusHost[$incident['state']];
                
                if(empty($issues[$state]['nb_hosts'])){
                    $issues[$state]['nb_hosts'] = 0;
                }
                
                if(empty($issues[$state]['total_impacts'])){
                    $issues[$state]['total_impacts'] = 0;
                }

                $hostsTemp = IncidentsRepository::formatDataForHeader($incident,'host');
                $hostsTemp['icon'] = HostRepositoryConfig::getIconImagePath($incident['host_id']);
                $hostsTemp['url'] = $router->getPathFor('/centreon-realtime/host/'.$incident['host_id']);
                $duration = Datetime::humanReadable(
                    time() - $incident['stimestamp'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $hostsTemp['since'] = $duration;
                $childIncidents = IncidentsRepository::getChildren($incident['issue_id']);
                $issues[$state]['objects']['hosts'][] = $hostsTemp;
                $issues[$state]['nb_hosts'] = ($issues[$state]['nb_hosts']) + 1;
                $issues[$state]['total_impacts'] = ($issues[$state]['total_impacts']) + count($childIncidents);
            }else{
                $state = $arrayStatusService[$incident['state']];
                
                if(empty($issues[$state]['nb_services'])){
                    $issues[$state]['nb_services'] = 0;
                }
                
                if(empty($issues[$state]['total_impacts'])){
                    $issues[$state]['total_impacts'] = 0;
                }

                $serviceTemp = IncidentsRepository::formatDataForHeader($incident,'service');
                $serviceTemp['icon'] = ServiceRepositoryConfig::getIconImage($incident['service_id']);
                $serviceTemp['url'] = $router->getPathFor('/centreon-realtime/service/'.$incident['service_id']);
                $duration = Datetime::humanReadable(
                    time() - $incident['stimestamp'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $serviceTemp['since'] = $duration;
                $childIncidents = IncidentsRepository::getChildren($incident['issue_id']);
                $issues[$state]['objects']['services'][] = $serviceTemp;
                $issues[$state]['nb_services'] = ($issues[$state]['nb_services']) + 1;
                $issues[$state]['total_impacts'] = ($issues[$state]['total_impacts']) + count($childIncidents);
            }

        }

        $hosts = \CentreonRealtime\Models\Host::getList();
        $configurationobjects = array();
        $configurationobjects['pending']['nb_hosts'] = 0;
        $configurationobjects['unreachable']['nb_hosts'] = 0;
        foreach($hosts as $host){
            $totalHosts++;
            $state = $arrayStatusHost[$host['state']];
            if($host['state'] == "4"){
                $configurationobjects['pending']['nb_hosts']++;
                
                $hostsTemp = $host;
                $duration = Datetime::humanReadable(
                    time() - $host['last_update'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $hostsTemp['icon'] = HostRepositoryConfig::getIconImagePath($host['host_id']);
                $hostsTemp['url'] = $router->getPathFor('/centreon-realtime/host/'.$host['host_id']);
                $hostsTemp['since'] = $duration;
                $hostsTemp['state'] = $state;
                $configurationobjects['pending']['objects']['hosts'][] = HostRepository::formatDataForHeader($hostsTemp);
                
            }else if($host['state'] == "3"){
                $configurationobjects['unreachable']['nb_hosts']++;
                $hostsTemp = $host;
                $duration = Datetime::humanReadable(
                    time() - $host['last_update'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $hostsTemp['icon'] = HostRepositoryConfig::getIconImagePath($host['host_id']);
                $hostsTemp['url'] = $router->getPathFor('/centreon-realtime/host/'.$host['host_id']);
                $hostsTemp['since'] = $duration;
                $hostsTemp['state'] = $state;
                $configurationobjects['unreachable']['objects']['hosts'][] = HostRepository::formatDataForHeader($hostsTemp);
            }
        }
        $services = \CentreonRealtime\Models\Service::getList();
        $configurationobjects['pending']['nb_services'] = 0;
        $configurationobjects['unknown']['nb_services'] = 0;
        foreach($services as $service){
            $totalServices++;
            $state = $arrayStatusService[$service['state']];
            if($service['state'] == "4"){
                $configurationobjects['pending']['nb_services']++;
                
                $serviceTemp = $service;
                
                $duration = Datetime::humanReadable(
                    time() - $service['last_update'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $serviceTemp['icon'] = ServiceRepositoryConfig::getIconImage($service['service_id']);
                $serviceTemp['url'] = $router->getPathFor('/centreon-realtime/service/'.$service['service_id']);
                $serviceTemp['since'] = $duration;
                $serviceTemp['state'] = $state;
                $configurationobjects['pending']['objects']['services'][] = ServiceRepository::formatDataForHeader($serviceTemp);
            }else if($service['state'] == "3"){
                $configurationobjects['unknown']['nb_services']++;
                $serviceTemp = $service;
                
                $duration = Datetime::humanReadable(
                    time() - $service['last_update'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $serviceTemp['icon'] = ServiceRepositoryConfig::getIconImage($service['service_id']);
                $serviceTemp['url'] = $router->getPathFor('/centreon-realtime/service/'.$service['service_id']);
                $serviceTemp['since'] = $duration;
                $serviceTemp['state'] = $state;
                $configurationobjects['unknown']['objects']['services'][] = ServiceRepository::formatDataForHeader($serviceTemp);
            }
        }

        //Get pollers infos
        $pollersStatus = PollerRepository::pollerStatus();
        $pollers = array();
        $pollers['stopped']['nb_pollers'] = 0;
        $pollers['unreachable']['nb_pollers'] = 0;
        foreach($pollersStatus as $poller){
            if($poller['running'] != "1"){
                $pollers['stopped']['nb_pollers']++;
                $pollerTemp = PollerRepository::formatDataForHeader($poller);
                $duration = Datetime::humanReadable(
                    time() - $poller['last_alive'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $pollerTemp['url'] = $router->getPathFor('/centreon-configuration/poller/'.$poller['instance_id']);
                $pollerTemp['since'] = $duration;
                $pollers['stopped']['objects'][] = $pollerTemp;
            }else if($poller['disconnect'] == "1"){
                $pollers['unreachable']['nb_pollers']++;
                $pollerTemp = PollerRepository::formatDataForHeader($poller);
                
                $duration = Datetime::humanReadable(
                    time() - $poller['last_alive'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $pollerTemp['url'] = $router->getPathFor('/centreon-configuration/poller/'.$poller['instance_id']);
                $pollerTemp['since'] = $duration;
                $pollers['unreachable']['objects'][] = $pollerTemp;
            }
        }
        

        $event->addStatus('issues', $issues);
        
        
        $states = $event->getStatus('states');
        if(empty($states)){
            $states = array();
        }
        $states['configurationObjects'] = $configurationobjects;
        $states['pollers'] = $pollers;
        $event->addStatus('states', $states);
        $event->addStatus('totalHosts', $totalHosts);
        $event->addStatus('totalServices', $totalServices);
    }
}
