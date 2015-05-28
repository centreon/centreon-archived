<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Models\Relation\ScheduledDowntime\Hosts as HostRelation;
use CentreonConfiguration\Models\Relation\ScheduledDowntime\HostsTags as HostTagRelation;
use CentreonConfiguration\Models\Relation\ScheduledDowntime\Services as ServiceRelation;
use CentreonConfiguration\Models\Relation\ScheduledDowntime\ServicesTags as ServiceTagRelation;

/**
 * Repository for scheduled downtimes
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package CentreonConfiguration
 * @subpackage Repository
 * @version 3.0.0
 */
class ScheduledDowntimeRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'downtime' => 'cfg_downtimes,dt_id,dt_name'
        ),
    );

    /**
     * Update periods for a scheduled downtime
     *
     * @param int $dtId The scheduled downtime id
     * @param array $periods The list of periods to insert
     */
    public static function updatePeriods($dtId, $periods)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        /* Delete old period for downtime period */
        $query = "DELETE FROM cfg_downtimes_periods WHERE dt_id = :dt_id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindValue(':dt_id', $dtId, \PDO::PARAM_INT);
        $stmt->execute();

        /* Prepare query for insert period */
        $query = "INSERT INTO cfg_downtimes_periods (dt_id, dtp_start_time, dtp_end_time, dtp_day_of_week, dtp_month_cycle, dtp_day_of_month, dtp_fixed, dtp_duration) VALUES (:id, :time_start, :time_end, :day_of_week, :month_cycle, :day_of_month, :fixed, :duration)";
        $stmt = $dbconn->prepare($query);
        foreach ($periods as $period) {
            $stmt->bindValue(':id', $dtId, \PDO::PARAM_INT);
            $stmt->bindValue(':time_start', $period['timeStart'], \PDO::PARAM_STR);
            $stmt->bindValue(':time_end', $period['timeEnd'], \PDO::PARAM_STR);
            $fixed = ($period['fixed'] == 'fixed' ? 1 : 0);
            $stmt->bindValue(':fixed', $fixed, \PDO::PARAM_STR);
            if ($period['duration'] === '') {
                $stmt->bindValue(':duration', null, \PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':duration', $period['duration'], \PDO::PARAM_INT);
            }

            /* Set information by type */
            switch ($period['periodType']) {
                case 'weekly':
                    $stmt->bindValue(':day_of_week', join(',', $period['days']), \PDO::PARAM_STR);
                    $stmt->bindValue(':month_cycle', 'all', \PDO::PARAM_STR);
                    $stmt->bindValue(':day_of_month', null, \PDO::PARAM_NULL);
                    break;
                case 'monthly':
                    $stmt->bindValue(':day_of_week', null, \PDO::PARAM_NULL);
                    $stmt->bindValue(':month_cycle', 'none', \PDO::PARAM_STR);
                    $stmt->bindValue(':day_of_month', join(',', $period['days']), \PDO::PARAM_STR);
                    break;
                case 'custom':
                    $stmt->bindValue(':day_of_week', null, \PDO::PARAM_NULL);
                    $stmt->bindValue(':month_cycle', null, \PDO::PARAM_NULL);
                    $stmt->bindValue(':day_of_month', json_encode($period['days']));
                    break;
            }

            $stmt->execute();
        }
    }

    /**
     * Get host linked to the scheduled downtime
     *
     * @param int $id The downtime id
     */
    public static function getHostRelation($id)
    {
        $hostList = HostRelation::getMergedParameters(
            array(),
            array('host_id', 'host_name'),
            -1,
            0,
            null,
            'ASC',
            array('cfg_downtimes_hosts_relations.dt_id' => $id),
            'AND'
        );
        return $hostList;
    }

    /**
     * Get host tag linked to the scheduled downtime
     *
     * @param int $id The downtime id
     */
    public static function getHostTagRelation($id)
    {
        $tagList = HostTagRelation::getMergedParameters(
            array(),
            array('tag_id', 'tagname'),
            -1,
            0,
            null,
            'ASC',
            array('cfg_downtimes_hosttags_relations.dt_id' => $id),
            'AND'
        );
        return $tagList;
    }

    /**
     * Get service linked to the scheduled downtime
     *
     * @param int $id The downtime id
     */
    public static function getServiceRelation($id)
    {
        $serviceList = ServiceRelation::getMergedParameters(
            array(),
            array('service_id', 'service_description'),
            -1,
            0,
            null,
            'ASC',
            array('cfg_downtimes_services_relations.dt_id' => $id),
            'AND'
        );
        return $serviceList;
    }

    /**
     * Get service tag linked to the scheduled downtime
     *
     * @param int $id The downtime id
     */
    public static function getServiceTagRelation($id)
    {
        $tagList = ServiceTagRelation::getMergedParameters(
            array(),
            array('tag_id', 'tagname'),
            -1,
            0,
            null,
            'ASC',
            array('cfg_downtimes_servicetags_relations.dt_id' => $id),
            'AND'
        );
        return $tagList;
    }

    /**
     * Get periods for a scheduled downtime
     *
     * @param int $id The downtime id
     */
    public static function getPeriods($id)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $periods = array();

        $query = "SELECT dtp_start_time, dtp_end_time, dtp_fixed, dtp_duration, dtp_month_cycle, dtp_day_of_month, dtp_day_of_week
            FROM cfg_downtimes_periods
            WHERE dt_id = :id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $period = array(
                'timeStart' => $row['dtp_start_time'],
                'timeEnd' => $row['dtp_end_time'],
                'fixed' => ($row['dtp_fixed'] == 1 ? 'fixed' : 'flexibled'),
                'duration' => (is_null($row['dtp_duration']) ? '' : $row['dtp_duration'])
            );

            switch ($row['dtp_month_cycle']) {
                case 'all':
                    $period['periodType'] = 'weekly';
                    $period['days'] = explode(',', $row['dtp_day_of_week']);
                    break;
                case 'none':
                    $period['periodType'] = 'monthly';
                    $period['days'] = explode(',', $row['dtp_day_of_month']);
                    break;
                case null:
                    $period['periodType'] = 'custom';
                    $period['days'] = json_decode($row['dtp_day_of_month'], true);
                    break;
            }
            $periods[] = $period;
        }

        return $periods;
    }
}
