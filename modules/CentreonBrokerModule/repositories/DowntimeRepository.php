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

namespace CentreonBroker\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Internal\Poller\WriteConfigFile;

/**
 * @author kevin duret <kduret@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class DowntimeRepository
{
    /**
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     */
    public function generate(& $filesList, $poller_id, $path, $filename)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        $enableField = array("tp_id" => 1);
        
        /* Init Content Array */
        $content = array();
        $timeperiodContent = array();
        $downtimes = array();
        
        /* Generate host downtimes */
        $query = 'SELECT d.dt_id, d.dt_name, dp.dtp_id, dp.dtp_fixed, dp.dtp_duration, dhr.host_host_id'
            . ' FROM cfg_downtimes d, cfg_downtimes_periods dp, cfg_downtimes_hosts_relations dhr'
            . ' WHERE d.dt_id=dhr.dt_id AND d.dt_id=dp.dt_id'
            . ' ORDER BY dt_id, dtp_id';
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        $downtimes = array_merge($downtimes, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        /* Generate host tag downtimes */
        $query = 'SELECT d.dt_id, d.dt_name, dp.dtp_id, dp.dtp_fixed, dp.dtp_duration, th.resource_id as host_host_id'
            . ' FROM cfg_downtimes d, cfg_downtimes_periods dp, cfg_downtimes_hosttags_relations dhr, cfg_tags_hosts th'
            . ' WHERE d.dt_id=dhr.dt_id AND d.dt_id=dp.dt_id AND dhr.host_tag_id=th.tag_id AND th.template_id=0'
            . ' ORDER BY dt_id, dtp_id';
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        $downtimes = array_merge($downtimes, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        /* Generate service downtimes */
        $query = 'SELECT d.dt_id, d.dt_name, dp.dtp_id, dp.dtp_fixed, dp.dtp_duration, hsr.host_host_id, dsr.service_service_id'
            . ' FROM cfg_downtimes d, cfg_downtimes_periods dp, cfg_downtimes_services_relations dsr, cfg_hosts_services_relations hsr'
            . ' WHERE d.dt_id=dsr.dt_id AND dsr.service_service_id=hsr.service_service_id AND d.dt_id=dp.dt_id'
            . ' ORDER BY dt_id, dtp_id';
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        $downtimes = array_merge($downtimes, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        /* Generate service tag downtimes */
        $query = 'SELECT d.dt_id, d.dt_name, dp.dtp_id, dp.dtp_fixed, dp.dtp_duration, hsr.host_host_id, hsr.service_service_id'
            . ' FROM cfg_downtimes d, cfg_downtimes_periods dp, cfg_downtimes_servicetags_relations dsr, cfg_tags_services ts, cfg_hosts_services_relations hsr'
            . ' WHERE d.dt_id=dsr.dt_id AND d.dt_id=dp.dt_id AND dsr.service_tag_id=ts.tag_id AND ts.template_id=0 AND ts.resource_id=hsr.service_service_id'
            . ' ORDER BY dt_id, dtp_id';
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        $downtimes = array_merge($downtimes, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        foreach ($downtimes as $downtime) {
            $tmp = array("type" => "downtime");
            $tmpData = array();

            $tmpData['host_id'] =  $downtime['host_host_id'];

            if (isset($downtime['service_service_id'])) {
                $tmpData['service_id'] =  $downtime['service_service_id'];
            }

            $tmpData['recurring_period'] = 'downtime_' . $downtime['dt_id'] . '_' . $downtime['dtp_id'];
            $tmpData['fixed'] = $downtime['dtp_fixed'];

            if ($downtime['dtp_fixed'] == 0) {
                $tmpData['duration'] = $downtime['dtp_duration'];
            }

            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }

        /* Write configuration file */
        WriteConfigFile::writeObjectFile($content, $path . $poller_id . "/" . $filename, $filesList, $user = "API");
        unset($content);
    }
}
