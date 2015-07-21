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

namespace CentreonEngine\Repository;

use Centreon\Internal\Di;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class TimePeriodRepository
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
        
        /* Get information into the database. */
        $query = "SELECT * FROM cfg_timeperiods ORDER BY tp_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();

        /* Get timeperiod exclusions */
        $queryExclusions = "SELECT ter.timeperiod_id, t.tp_name FROM cfg_timeperiods_exclude_relations ter, cfg_timeperiods t WHERE ter.timeperiod_exclude_id=t.tp_id";
        $stmtExclusions = $dbconn->prepare($queryExclusions);
        $stmtExclusions->execute();
        $timeperiodExclusions = $stmtExclusions->fetchAll(\PDO::FETCH_ASSOC);

        /* Get timeperiod inclusions */
        $queryInclusions = "SELECT tir.timeperiod_id, t.tp_name FROM cfg_timeperiods_include_relations tir, cfg_timeperiods t WHERE tir.timeperiod_include_id=t.tp_id";
        $stmtInclusions = $dbconn->prepare($queryInclusions);
        $stmtInclusions->execute();
        $timeperiodInclusions = $stmtInclusions->fetchAll(\PDO::FETCH_ASSOC);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "timeperiod");
            $tmpData = array();

            foreach ($row as $key => $value) {
                if ($key == 'organization_id' || $key == 'tp_slug') {
                    continue;
                }
                if ($key == 'tp_name') {
                    $key = "timeperiod_name";
                }
                if (!isset($enableField[$key]) && $value != "") {
                    $key = str_replace("tp_", "", $key);
                    $tmpData[$key] = $value;
                }
            }

            /* Generate exclude parameter */
            $exclusions = array();
            foreach ($timeperiodExclusions as $timeperiodExclusion) {
                if ($row['tp_id'] == $timeperiodExclusion['timeperiod_id']) {
                    $exclusions[] = $timeperiodExclusion['tp_name'];
                }
            }
            if (count($exclusions)) {
                $tmpData['exclude'] = implode(',', $exclusions);
            }

            /* Generate include parameter */
            /*$inclusions = array();
            foreach ($timeperiodInclusions as $timeperiodInclusion) {
                if ($row['tp_id'] == $timeperiodInclusion['timeperiod_id']) {
                    $inclusions[] = $timeperiodInclusion['tp_name'];
                }
            }
            if (count($inclusions)) {
                $tmpData['include'] = implode(',', $inclusions);
            }*/

            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }

        /* Write Check-Command configuration file */
        WriteConfigFileRepository::writeObjectFile($content, $path . $poller_id . "/objects.d/" . $filename, $filesList, $user = "API");
        unset($content);
    }
}
