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
        
        /* Generate host downtimes. */
        $query = 'SELECT d.dt_id, d.dt_name, dhr.host_host_id'
            . ' FROM cfg_downtimes d, cfg_downtimes_hosts_relations dhr'
            . ' WHERE d.dt_id=dhr.dt_id';
        $stmt = $dbconn->prepare($query);
        $stmt->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "downtime");
            $tmpData = array();

            $tmpData['host_id'] =  $row['host_host_id'];
            $tmpData['recurring_timeperiod'] = 'downtime_' . $row['dt_id'];

            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }

        /* Generate service downtimes. */
        $query = 'SELECT d.dt_id, d.dt_name, hsr.host_host_id, dsr.service_service_id'
            . ' FROM cfg_downtimes d, cfg_downtimes_services_relations dsr, cfg_hosts_services_relations hsr'
            . ' WHERE d.dt_id=dsr.dt_id AND dsr.service_service_id=hsr.service_service_id';
        $stmt = $dbconn->prepare($query);
        $stmt->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "downtime");
            $tmpData = array();

            $tmpData['host_id'] =  $row['host_host_id'];
            $tmpData['service_id'] =  $row['service_service_id'];
            $tmpData['recurring_timeperiod'] = 'downtime_' . $row['dt_id'];

            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }

        /* Write configuration file */
        WriteConfigFile::writeObjectFile($content, $path . $poller_id . "/" . $filename, $filesList, $user = "API");
        unset($content);
    }
}
