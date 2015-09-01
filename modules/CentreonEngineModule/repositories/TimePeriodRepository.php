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

namespace CentreonEngine\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Internal\Poller\WriteConfigFile;

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
        WriteConfigFile::writeObjectFile($content, $path . $poller_id . "/objects.d/" . $filename, $filesList, $user = "API");
        unset($content);
    }
}
