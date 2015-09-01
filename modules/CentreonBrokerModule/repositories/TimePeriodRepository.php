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
class TimePeriodRepository
{
    static $week = array (
        '1' => 'monday',
        '2' => 'tuesday',
        '3' => 'wednesday',
        '4' => 'thrusday',
        '5' => 'friday',
        '6' => 'saturday',
        '7' => 'sunday'
    );

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
        $queryDowntimePeriods = 'SELECT dt_id, dtp_id, dtp_start_time, dtp_end_time, dtp_duration, dtp_day_of_week, dtp_day_of_month'
            . ' FROM cfg_downtimes_periods'
            . ' ORDER BY dt_id,dtp_id';
        $stmtDowntimePeriods = $dbconn->prepare($queryDowntimePeriods);
        $stmtDowntimePeriods->execute();
        $result = $stmtDowntimePeriods->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_ASSOC);

        foreach ($result as $key => $value) {
            $tmp = array("type" => "timeperiod");

            foreach ($value as $subvalue) {
                $tmpData = array();
                $tmpData['timeperiod_name'] = 'downtime_' . $key . '_' . $subvalue['dtp_id'];
                $subvalue['dtp_start_time'] = preg_replace('/(\d+:\d+):\d+/', '$1', $subvalue['dtp_start_time']);
                $subvalue['dtp_end_time'] = preg_replace('/(\d+:\d+):\d+/', '$1', $subvalue['dtp_end_time']);

                if (isset($subvalue['dtp_day_of_week'])) {
                    $dayOfWeek = explode(',', $subvalue['dtp_day_of_week']);
                    foreach ($dayOfWeek as $day) {
                        $tmpData[static::$week[$day]] = $subvalue['dtp_start_time'] . ',' . $subvalue['dtp_end_time'];
                    }
                }

                if (isset($subvalue['dtp_day_of_month']) && preg_match('/\d$/', $subvalue['dtp_day_of_month'])) {
                    $tmpData['day' . ' ' . $subvalue['dtp_day_of_month']] = $subvalue['dtp_start_time'] . ',' . $subvalue['dtp_end_time'];
                } else if (isset($subvalue['dtp_day_of_month'])){
                    $daysOfMonth = json_decode($subvalue['dtp_day_of_month'], true);
                    foreach ($daysOfMonth as $dayOfMonth) {
                        $tmpData[static::$week[$dayOfMonth['wday']] . ' ' . $dayOfMonth['nthDay']] = $subvalue['dtp_start_time'] . ',' . $subvalue['dtp_end_time'];
                    }
                }

                $tmp["content"] = $tmpData;
                $content[] = $tmp;
            }

        }

        /* Write Check-Command configuration file */
        WriteConfigFile::writeObjectFile($content, $path . $poller_id . "/" . $filename, $filesList, $user = "API");
        unset($content);
    }
}
