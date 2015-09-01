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
        $arrayStatusHost = array('success','critical','unreachable','','pending');
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
                if(!isset($issues[$state]['objects']['hosts']) ||  count($issues[$state]['objects']['hosts']) < 5){
                    $hostsTemp = IncidentsRepository::formatDataForHeader($incident,'host');
                    $hostsTemp['icon'] = HostRepositoryConfig::getIconImagePath($incident['host_id']);
                    $hostsTemp['url'] = $router->getPathFor('/centreon-realtime/host/'.$incident['host_id']);
                    $duration = Datetime::humanReadable(
                        time() - $incident['stimestamp'],
                        Datetime::PRECISION_FORMAT,
                        2
                    );
                    $hostsTemp['since'] = $duration;
                    $issues[$state]['objects']['hosts'][] = $hostsTemp;
                }

                $childIncidents = IncidentsRepository::getChildren($incident['issue_id']);
                
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

                if(!isset($issues[$state]['objects']['services']) || count($issues[$state]['objects']['services']) < 5){
                    $serviceTemp = IncidentsRepository::formatDataForHeader($incident,'service');
                    $serviceTemp['icon'] = ServiceRepositoryConfig::getIconImage($incident['service_id']);
                    $serviceTemp['url'] = $router->getPathFor('/centreon-realtime/service/'.$incident['service_id']);
                    $duration = Datetime::humanReadable(
                        time() - $incident['stimestamp'],
                        Datetime::PRECISION_FORMAT,
                        2
                    );
                    $serviceTemp['since'] = $duration;
                    $issues[$state]['objects']['services'][] = $serviceTemp;
                }
                $childIncidents = IncidentsRepository::getChildren($incident['issue_id']);
                
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
        $event->addStatus('totalhosts', $totalHosts);
        $event->addStatus('totalservices', $totalServices);
    }
}
