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
