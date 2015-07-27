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
        $incidents = IncidentsRepository::getIncidents();
        $hosts = array();
        $services = array();
        $impactHosts = 0;
        $impactServices = 0;
        foreach($incidents as $key=>$incident){
            
            $issue_duration = Datetime::humanReadable(
                time() - $incident['stimestamp'],
                Datetime::PRECISION_FORMAT,
                2
            );
            if(!empty($incident['host_id']) || $incident['host_id'] == "0"){
                $hostsTemp = $incident;
                $hostsTemp['icon'] = HostRepositoryConfig::getIconImagePath($incident['host_id']);
                $hostsTemp['url'] = $router->getPathFor('/centreon-realtime/host/'.$incident['host_id']);
                $hostsTemp['state'] = ServiceRepository::countAllStatusForHost($incident['host_id']);
                $hostsTemp['issue_duration'] = $issue_duration;
                $childIncidentsHost = IncidentsRepository::getChildren($incident['issue_id']);
                $impactHosts += count($childIncidentsHost);
                $hosts[] = $hostsTemp;
            }
            
            if(!empty($incident['service_id']) || $incident['service_id'] == "0"){
                $serviceTemp = $incident;
                $serviceTemp['icon'] = ServiceRepositoryConfig::getIconImage($incident['host_id']);
                $serviceTemp['url'] = $router->getPathFor('/centreon-realtime/host/'.$incident['host_id']);
                $serviceTemp['issue_duration'] = $issue_duration;
                $serviceTemp['state'] = ServiceRepository::countAllStatusForHost($incident['host_id']);
                $childIncidentsService = IncidentsRepository::getChildren($incident['issue_id']);
                $impactServices += count($childIncidentsService);
                $services[] = $serviceTemp;
            }
        }
        
        $pollers = PollerRepository::pollerStatus();
        $event->addStatus('hosts', $hosts);
        $event->addStatus('nb_incidents_hosts', count($hosts));
        $event->addStatus('services', $services);
        $event->addStatus('nb_incidents_services', count($services));
        $event->addStatus('pollers', $pollers);
        $event->addStatus('impact_hosts', $impactHosts);
        $event->addStatus('impact_services', $impactServices);

        /*
        $values = array(
            'services' => array(
                'unknown' => 0,
                'warning' => 0,
                'critical' => 0
            ),
            'hosts' => array(
                'unknown' => 0,
                'warning' => 0,
                'critical' => 0
            ),
            'pollers' => array(
                'activity' => 0,
                'stopped' => 0,
                'latency' => 0
            )
        );
        $db = Di::getDefault()->get('db_centreon');

        $query = "SELECT COUNT(service_id) as nb, state
            FROM rt_services
            WHERE state_type = 1
                AND state IN (1, 2, 3)
                AND acknowledged = 0
                OR acknowledged is null
            GROUP BY state";
        $stmt = $db->query($query);
        while ($row = $stmt->fetch()) {
            if ($row['state'] == 1) {
                $values['services']['warning'] = $row['nb'];
            } elseif ($row['state'] == 2) {
                $values['services']['critical'] = $row['nb'];
            } elseif ($row['state'] == 3) {
                $values['services']['unknown'] = $row['nb'];
            }
        }
        $event->addStatus('service', $values['services']);

        $query = "SELECT COUNT(host_id) as nb, state
            FROM rt_hosts
            WHERE state_type = 1
                AND state IN (1, 2, 3)
                AND acknowledged = 0
                OR acknowledged is null
            GROUP BY state";
        $stmt = $db->query($query);
        while ($row = $stmt->fetch()) {
            if ($row['state'] == 1) {
                $values['host']['warning'] = $row['nb'];
            } elseif ($row['state'] == 2) {
                $values['host']['down'] = $row['nb'];
            } elseif ($row['state'] == 3) {
                $values['host']['unknown'] = $row['nb'];
            }
        }
        $event->addStatus('host', $values['hosts']);

        $query = "SELECT last_alive, running
            FROM rt_instances
            WHERE deleted != 1";
        $stmt = $db->query($query);
        $now = time();
        while ($row = $stmt->fetch()) {
            if ($row['running'] == 0) {
                $values['pollers']['stopped']++;
            } elseif ($row['last_alive'] - $now > 60) {
                $values['pollers']['activity']++;
            }
        }
        $event->addStatus('poller', $values['pollers']);
     */
    }
}
