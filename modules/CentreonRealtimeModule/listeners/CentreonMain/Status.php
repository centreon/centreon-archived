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
        $status = array();
        $pending = array();
        $unknown = array();
        $pending['hosts'] = array();
        $pending['services'] = array();
        $unknown['services'] = array();
        $pollerArray = array();
        $stopped = 0;
        $unreachable = 0;
        
        //Get total number of hosts
        $tHosts = \CentreonRealtime\Models\Host::getList("host_id",
                                        1
                                    );
        $totalHosts = $tHosts[0]['host_id'];
        //Get total number of services
        $tServices = \CentreonRealtime\Models\Service::getList("service_id",
                                                1
                                            );
        $totalServices = $tServices[0]['service_id'];
        
        
        // Get warning and critical incidents
        $incidents = IncidentsRepository::getIncidents();
        foreach($incidents as $incident){

            $issue_duration = Datetime::humanReadable(
                time() - $incident['stimestamp'],
                Datetime::PRECISION_FORMAT,
                2
            );
            
            
            if(!empty($incident['host_id']) || $incident['host_id'] == "0"){
                $state = $arrayStatusHost[$incident['state']];
                if(empty($status[$state]['totalHostIncidents'])){
                    $status[$state]['totalHostIncidents'] = 0;
                }
                if(empty($status[$state]['totalServiceIncidents'])){
                    $status[$state]['totalServiceIncidents'] = 0;
                }
                if(empty($status[$state]['totalImpact'])){
                    $status[$state]['totalImpact'] = 0;
                }
                $hostsTemp = $incident;
                $hostsTemp['icon'] = HostRepositoryConfig::getIconImagePath($incident['host_id']);
                $hostsTemp['url'] = $router->getPathFor('/centreon-realtime/host/'.$incident['host_id']);
                $hostsTemp['states'] = ServiceRepository::countAllStatusForHost($incident['host_id']);
                $hostsTemp['issue_duration'] = $issue_duration;
                $hostsTemp['state'] = $state;
                $childIncidentsHost = IncidentsRepository::getChildren($incident['issue_id']);
                $status[$state]['hosts'][] = HostRepository::formatDataForHeader($hostsTemp);
                $status[$state]['totalImpact'] = ($status[$state]['totalImpact']) + count($childIncidentsHost);
                $status[$state]['totalHostIncidents'] = ($status[$state]['totalHostIncidents']) + 1;
            }
            
            if(!empty($incident['service_id']) || $incident['service_id'] == "0"){
                $state = $arrayStatusService[$incident['state']];
                if(empty($status[$state]['totalHostIncidents'])){
                    $status[$state]['totalHostIncidents'] = 0;
                }
                if(empty($status[$state]['totalServiceIncidents'])){
                    $status[$state]['totalServiceIncidents'] = 0;
                }
                if(empty($status[$state]['totalImpact'])){
                    $status[$state]['totalImpact'] = 0;
                }
                $serviceTemp = $incident;
                $serviceTemp['icon'] = ServiceRepositoryConfig::getIconImage($incident['service_id']);
                $serviceTemp['url'] = $router->getPathFor('/centreon-realtime/service/'.$incident['service_id']);
                $serviceTemp['issue_duration'] = $issue_duration;
                //$serviceTemp['states'] = ServiceRepository::getStatus($incident['host_id'],$incident['service_id']);
                $serviceTemp['state'] = $state;
                $childIncidentsService = IncidentsRepository::getChildren($incident['issue_id']);
                $status[$state]['services'][] = ServiceRepository::formatDataForHeader($serviceTemp);
                $status[$state]['totalImpact'] = ($status[$state]['totalImpact']) + count($childIncidentsService);
                $status[$state]['totalServiceIncidents'] = ($status[$state]['totalServiceIncidents']) + 1;
            }
        }
        
        
        // Get Host and service in "pending" state and service in "unknown" 
        $hostsPending = \CentreonRealtime\Models\Host::getList("*",
                                                -1,
                                                0,
                                                null,
                                                "ASC",
                                                array('state'=>'4')
                                            );
        $servicesPendingUnknown = \CentreonRealtime\Models\Service::getList("*",
                                                -1,
                                                0,
                                                null,
                                                "ASC",
                                                array('state'=>'4','state'=>'3'),
                                                "OR"
                                            );

        foreach($servicesPendingUnknown as $servicePendingUnknown){
            $duration = Datetime::humanReadable(
                time() - $servicePendingUnknown['last_update'],
                Datetime::PRECISION_FORMAT,
                2
            );
            if($servicePendingUnknown['state'] == "4"){
                $serviceTemp = $servicePendingUnknown;
                $serviceTemp['icon'] = ServiceRepositoryConfig::getIconImage($servicePendingUnknown['service_id']);
                $serviceTemp['url'] = $router->getPathFor('/centreon-realtime/service/'.$servicePendingUnknown['service_id']);
                $serviceTemp['issue_duration'] = $duration;
                $pending['services'][] = ServiceRepository::formatDataForHeader($serviceTemp);
            }else if($servicePendingUnknown['state'] == "3"){
                $serviceTemp = $servicePendingUnknown;
                $serviceTemp['icon'] = ServiceRepositoryConfig::getIconImage($servicePendingUnknown['service_id']);
                $serviceTemp['url'] = $router->getPathFor('/centreon-realtime/service/'.$servicePendingUnknown['service_id']);
                $serviceTemp['issue_duration'] = $duration;
                $unknown['services'][] = ServiceRepository::formatDataForHeader($serviceTemp);
            }
        }
        
        foreach($hostsPending as $hostPending){
                $duration = Datetime::humanReadable(
                    time() - $hostPending['last_update'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $hostsTemp = $hostPending;
                $hostsTemp['icon'] = HostRepositoryConfig::getIconImagePath($hostPending['host_id']);
                $hostsTemp['url'] = $router->getPathFor('/centreon-realtime/host/'.$hostPending['host_id']);
                $hostsTemp['issue_duration'] = $duration;
                $pending['hosts'][] = HostRepository::formatDataForHeader($hostsTemp);
        }
        $pending['totalHost'] = count($pending['hosts']);
        $pending['totalService'] = count($pending['services']);
        $unknown['total'] = count($unknown['services']);
        
        
        
        //Get pollers infos
        $pollers = PollerRepository::pollerStatus();
        foreach($pollers as $poller){
            if($poller['running'] != "1"){
                $stopped++;
            }
            if($poller['disconnect'] == "1"){
                $unreachable++;
            }
        }
        $pollerArray['stopped'] = $stopped;
        $pollerArray['unreachable'] = $unreachable;
        $pollerArray['pollers'] = $pollers;
        $event->addStatus('status', $status);
        $event->addStatus('pollers', $pollerArray);
        $event->addStatus('pending', $pending);
        $event->addStatus('unknown', $unknown);
        $event->addStatus('totalHosts', $totalHosts);
        $event->addStatus('totalServices', $totalServices);
        
        
        
        
    }
}
