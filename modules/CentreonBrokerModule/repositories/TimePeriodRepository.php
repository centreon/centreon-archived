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
class TimePeriodRepository
{
    static $week = array (
        '1' => 'sunday',
        '2' => 'monday',
        '3' => 'tuesday',
        '4' => 'wednesday',
        '5' => 'thrusday',
        '6' => 'friday',
        '7' => 'saturday'
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
                if (isset($subvalue['dtp_day_of_week'])) {
                    $dayOfWeek = explode(',', $subvalue['dtp_day_of_week']);
                    foreach ($dayOfWeek as $day) {
                        $tmpData[static::$week[$day]] = $subvalue['dtp_start_time'] . ',' . $subvalue['dtp_end_time'];
                    }
                }
                if (isset($subvalue['dtp_day_of_month'])) {
                    $tmpData['day'] = $subvalue['dtp_day_of_month'];
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
