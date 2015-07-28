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
        $totalHosts = 0;
        $totalServices = 0;
        foreach($arrayStatusHost as $statusHost){
            $status[$statusHost]['totalHostIncidents'] = 0;
            $status[$statusHost]['totalImpact'] = 0;
        }
        foreach($arrayStatusService as $statusService){
            $status[$statusService]['totalServiceIncidents'] = 0;
            $status[$statusService]['totalImpact'] = 0;
        }

        $hosts = \CentreonRealtime\Models\Host::getList();
        foreach($hosts as $host){
            $totalHosts++;
            if($host['state'] !== "0"){
                $state = $arrayStatusHost[$host['state']];
                $incidents = IncidentsRepository::getIncidents(null,'DESC',null,array('i.host_id'=>$host['host_id']));
                foreach($incidents as $incident){
                    $childIncidentsHost = IncidentsRepository::getChildren($incident['issue_id']);
                    $status[$state]['totalImpact'] += count($childIncidentsHost);
                }

                $hostsTemp = $host;

                $duration = Datetime::humanReadable(
                    time() - $host['last_update'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $hostsTemp['icon'] = HostRepositoryConfig::getIconImagePath($host['host_id']);
                $hostsTemp['url'] = $router->getPathFor('/centreon-realtime/host/'.$host['host_id']);
                $hostsTemp['states'] = ServiceRepository::countAllStatusForHost($host['host_id']);
                $hostsTemp['issue_duration'] = $duration;
                $hostsTemp['state'] = $state;
                $status[$state]['hosts'][] = HostRepository::formatDataForHeader($hostsTemp);
                $status[$state]['totalHostIncidents'] = ($status[$state]['totalHostIncidents']) + 1;
            }
        }
        $services = \CentreonRealtime\Models\Service::getList();
        foreach($services as $service){
            $totalServices++;
            if($service['state'] !== "0"){
                $state = $arrayStatusService[$service['state']];
                $incidents = IncidentsRepository::getIncidents(null,'DESC',null,array('i.service_id'=>$service['service_id']));
                
                foreach($incidents as $incident){
                    $childIncidentsService = IncidentsRepository::getChildren($incident['issue_id']);
                    $status[$state]['totalImpact'] += count($childIncidentsService);
                }
                
                $serviceTemp = $service;
                
                $duration = Datetime::humanReadable(
                    time() - $service['last_update'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
                $serviceTemp['icon'] = ServiceRepositoryConfig::getIconImage($service['service_id']);
                $serviceTemp['url'] = $router->getPathFor('/centreon-realtime/service/'.$service['service_id']);
                $serviceTemp['issue_duration'] = $duration;
                $serviceTemp['state'] = $state;
                $status[$state]['services'][] = ServiceRepository::formatDataForHeader($serviceTemp);
                $status[$state]['totalServiceIncidents'] = ($status[$state]['totalServiceIncidents']) + 1;
            }
        }

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
        $event->addStatus('totalHosts', $totalHosts);
        $event->addStatus('totalServices', $totalServices);
    }
}
