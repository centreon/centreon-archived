<?php
/**
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once _CENTREON_PATH_ . 'www/class/centreonHost.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonGMT.class.php';

/**
 * Class for management downtime with ndo broker
 *
 * @see CentreonDowntime
 */
class CentreonDowntimeBroker extends CentreonDowntime
{
    private $dbb;

    /**
     * Constructor
     *
     * @param CentreonDb $pearDB
     * @param string $varlib
     */
    public function __construct($pearDB, $varlib = null)
    {
        parent::__construct($pearDB, $varlib);
        $this->dbb = new CentreonDB('centstorage');
        $this->initPeriods();
    }

    /**
     * Get the list of reccurrent downtime after now
     *
     * Return array
     *   array(
     *      'services' => array(
     *          0 => array('Host 1', 'Service 1')
     *      ),
     *      'hosts' => array(
     *         0 => array('Host 1')
     *      )
     *  )
     *
     * @return array A array with host and services for downtime, or false if in error
     */
    public function getSchedDowntime()
    {
        $list = array('hosts' => array(), 'services' => array());
        $query = "SELECT d.internal_id as internal_downtime_id,
						 h.name as name1,
						 s.description as name2
			FROM downtimes d, hosts h
			LEFT JOIN services s ON s.host_id = h.host_id
			WHERE d.host_id = h.host_id AND d.start_time > NOW() AND d.comment_data LIKE '[Downtime cycle%'";
        $res = $this->dbb->query($query);
        if (PEAR::isError($res)) {
            return false;
        }
        while ($row = $res->fetchRow()) {
            if (isset($row['name2']) && $row['name2'] != "") {
                $list['services'] = array('host_name' => $row['name1'], 'service_name' => $row['name2']);
            } elseif (isset($row['name1']) && $row['name1'] != "") {
                $list['hosts'] = array('host_name' => $row['name1']);
            }
        }
        return $list;
    }

    /**
     * Get the NDO internal ID
     *
     * @param string $oname1 The first object name (host_name)
     * @param int $start_time The timestamp for starting downtime
     * @param int $dt_id The downtime id
     * @param string $oname2 The second object name (service_name), is null if search a host
     * @return int
     */
    public function getDowntimeInternalId($oname1, $start_time, $dt_id, $oname2 = null)
    {
        $query = "SELECT d.internal_id as internal_downtime_id
        		  FROM downtimes d, hosts h ";
        if (isset($oname2) && $oname2 != "") {
            $query .= ", services s ";
        }
        $query .= "WHERE d.host_id = h.host_id
        		  AND d.start_time = " .$this->dbb->escape($start_time). "
        		  AND d.comment_data = '[Downtime cycle #".$dt_id."]'
        		  AND h.name = '".$this->dbb->escape($oname1)."' ";
        if (isset($oname2) && $oname2 != "") {
            $query .= " AND h.host_id = s.host_id ";
            $query .= " AND s.description = '".$this->dbb->escape($oname2)."' ";
        }
        $res = $this->dbb->query($query);
        if (PEAR::isError($res)) {
            return false;
        }
        $row = $res->fetchRow();
        return $row['internal_downtime_id'];
    }

    public function doSchedule($id, $currentHostDate, $start, $end)
    {
        $periods = $this->getPeriods($id);
        $listSchedule = array();

        /* Convert HH::mm::ss to HH:mm */
        $start = substr($start, 0, strrpos($start, ':'));
        $end = substr($end, 0, strrpos($end, ':'));

        foreach ($periods as $period) {
            if ($period['start_time'] != $start || $period['end_time'] != $end) {
                continue;
            }

            $add = false;

            $start_tomorrow = false;
            if ($period['start_time'] == '00:00') {
                $start_tomorrow = true;
            }

            $dateOfMonth = $currentHostDate->format('w');
            if ($dateOfMonth == 0) {
                $dateOfMonth = 7;
            }
            if ($start_tomorrow) {
                if ($dateOfMonth == 7) {
                    $dateOfMonth = 1;
                } else {
                    $dateOfMonth++;
                }
            }

            if ($period['month_cycle'] == 'none') {
                $dateOfMonth = $currentHostDate->format('j');

                if (in_array($dateOfMonth, $period['day_of_month'])) {
                    $add = true;
                }
            } elseif ($period['month_cycle'] == 'all') {
                if (in_array($dateOfMonth, $period['day_of_week'])) {
                    $add = true;
                }
            } else {
                if ($dateOfMonth == $period['day_of_week']) {
                    $monthName = $currentHostDate->format('F');
                    $year = $currentHostDate->format('Y');
                    $dayShortName = $currentHostDate->format('D');
                    $dayInMonth = date(
                        'd',
                        strtotime($period['month_cycle'] . ' ' . $dayShortName . ' ' . $monthName . ' ' . $year)
                    );

                    if ($dayInMonth == $currentHostDate->format('d')) {
                        $add = true;
                    }
                }
            }

            if ($add) {
                $timestamp_start = new DateTime();
                $timestamp_start->setTimezone($currentHostDate->getTimezone());
                $sStartTime = explode(":", $period['start_time']);
                if (count($sStartTime) != 2) {
                    throw new Exception("Invalid format ".$period['start_time']);
                }

                $timestamp_start->setTime($sStartTime[0], $sStartTime[1], '00');
                if ($start_tomorrow) {
                    $timestamp_start->add(new DateInterval('P1D'));
                }


                $oInterval = $currentHostDate->diff($timestamp_start);
                $interval =  $oInterval->days * 86400 + $oInterval->h * 3600 + $oInterval->i * 60 + $oInterval->s;
                if ($oInterval->invert) {
                    $interval = - $interval;
                }

                # schedule downtime if approaching
                if ($interval > 0 && $interval < _DELAY_) {
                    $timestamp_stop = new DateTime();
                    $timestamp_stop->setTimezone($currentHostDate->getTimezone());
                    if ($start_tomorrow) {
                        $timestamp_stop->add(new DateInterval('P1D'));
                    }
                    $sEndTime = explode(":", $period['end_time']);
                    if (count($sEndTime) != 2) {
                        throw new Exception("Invalid format ".$period['end_time']);
                    }

                    $timestamp_stop->setTime($sEndTime[0], $sEndTime[1], '00');

                    $listSchedule[] = array($timestamp_start->format('c'), $timestamp_stop->format('c'));
                }
            }
        }

        return $listSchedule;
    }

    public function isWeeklyApproachingDowntime($startDelay, $endDelay, $daysOfWeek, $startTimestamp, $endTimestamp, $tomorrow)
    {
        $isApproaching = false;

        if ($tomorrow) {
            $currentDayOfWeek = $endDelay->format('w');
        } else {
            $currentDayOfWeek = $startDelay->format('w');
        }

        $daysOfWeek = explode(',', $daysOfWeek);
        foreach ($daysOfWeek as $dayOfWeek) {
            if ($dayOfWeek == 7) {
                $dayOfWeek = 0;
            }
            if ($currentDayOfWeek == $dayOfWeek &&
                $startTimestamp >= $startDelay->getTimestamp() &&
                $startTimestamp <= $endDelay->getTimestamp()) {
                $isApproaching = true;
            }
        }

        return $isApproaching;
    }

    public function isMonthlyApproachingDowntime($startDelay, $endDelay, $daysOfMonth, $startTimestamp, $endTimestamp, $tomorrow)
    {
        $isApproaching = false;

        if ($tomorrow) {
            $currentDayOfMonth = $endDelay->format('d');
        } else {
            $currentDayOfMonth = $startDelay->format('d');
        }

        if (preg_match('/^0(\d)$/', $currentDayOfMonth, $matches)) {
            $currentDayOfMonth = $matches[1];
        }

        $daysOfMonth = explode(',', $daysOfMonth);
        foreach ($daysOfMonth as $dayOfMonth) {
            if ($currentDayOfMonth == $dayOfMonth &&
                $startTimestamp >= $startDelay->getTimestamp() &&
                $startTimestamp <= $endDelay->getTimestamp()) {
                $isApproaching = true;
            }
        }

        return $isApproaching;
    }

    public function isSpecificDateDowntime($startDelay, $endDelay, $dayOfWeek, $cycle, $startTimestamp, $endTimestamp, $tomorrow)
    {
        $isApproaching = false;

        if ($dayOfWeek == 7) {
            $dayOfWeek = 0;
        }

        $daysOfWeekAssociation = array(
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday'
        );
        $dayOfWeek = $daysOfWeekAssociation[$dayOfWeek];

        if ($tomorrow) {
            $currentMonth = $endDelay->format('M');
            $currentYear =  $endDelay->format('Y');
            $currentDay = $endDelay->format('Y-m-d');
        } else {
            $currentMonth = $startDelay->format('M');
            $currentYear = $startDelay->format('Y');
            $currentDay = $startDelay->format('Y-m-d');
        }

        $cycleDay = new DateTime($cycle . ' ' . $dayOfWeek . ' of ' . $currentMonth . ' ' . $currentYear);
        $cycleDay = $cycleDay->format('Y-m-d');

        if ($currentDay == $cycleDay &&
            $startTimestamp >= $startDelay->getTimestamp() &&
            $startTimestamp <= $endDelay->getTimestamp()) {
            $isApproaching = true;
        }

        return $isApproaching;
    }

    private function getTimestampFromHourMinute($hourMinute, $timezone, $tomorrow)
    {
        list($hour, $minute) = explode(':', $hourMinute);
        $currentDate = new DateTime();
        $currentDate->setTimezone($timezone);
        $currentDate->setTime($hour, $minute, '00');
        if ($tomorrow) {
            $currentDate->add(new DateInterval('P1D'));
        }

        return $currentDate->getTimestamp();
    }

    public function getApproachingDowntimes($delay)
    {
        $approachingDowntimes = array();

        $downtimes = $this->getDowntime();

        $hostObj = new CentreonHost($this->db);
        $gmtObj = new CentreonGMT($this->db);

        $delayInterval = new DateInterval('PT' . $delay . 'S');

        $startDelay =  new DateTime();
        $endDelay = new DateTime();
        $endDelay->add($delayInterval);

        foreach ($downtimes as $downtime) {

            $currentHostDate = $gmtObj->getHostCurrentDatetime($downtime['host_id']);
            $timezone = $currentHostDate->getTimezone();
            $startDelay->setTimezone($timezone);
            $endDelay->setTimezone($timezone);

            $midnightDate = new DateTime();
            $midnightDate->setTimezone($timezone);
            $midnightDate->setTime('00', '00', '00');
            $midnightPlusDelayDate = new DateTime();
            $midnightPlusDelayDate->setTimezone($timezone);
            $midnightPlusDelayDate->setTime('00', '00', '00');
            $midnightPlusDelayDate = $midnightPlusDelayDate->add($delayInterval);
            $midnight = $midnightDate->format('H:i');
            $midnightPlusDelay = $midnightPlusDelayDate->format('H:i');

            /* Convert HH::mm::ss to HH:mm */
            $downtime['dtp_start_time'] = substr($downtime['dtp_start_time'], 0, strrpos($downtime['dtp_start_time'], ':'));
            $downtime['dtp_end_time'] = substr($downtime['dtp_end_time'], 0, strrpos($downtime['dtp_end_time'], ':'));

            $tomorrow = false;
            if (strtotime($downtime['dtp_start_time']) >= strtotime($midnight) &&
                strtotime($downtime['dtp_start_time']) <= strtotime($midnightPlusDelay) && 
                strtotime($startDelay->format('H:i')) < strtotime($midnight)) {
                $tomorrow = true;
            }

            $startTimestamp = $this->getTimestampFromHourMinute($downtime['dtp_start_time'], $timezone, $tomorrow);
            $endTimestamp = $this->getTimestampFromHourMinute($downtime['dtp_end_time'], $timezone, $tomorrow);

            $approaching = false;
            if (preg_match('/^\d(,\d)*$/', $downtime['dtp_day_of_week']) && $downtime['dtp_month_cycle'] == 'none') {
                $approaching = $this->isWeeklyApproachingDowntime(
                    $startDelay,
                    $endDelay,
                    $downtime['dtp_day_of_week'],
                    $startTimestamp,
                    $endTimestamp,
                    $tomorrow
                );
            } else if (preg_match('/^\d+(,\d+)*$/', $downtime['dtp_day_of_month'])) {
                $approaching = $this->isMonthlyApproachingDowntime(
                    $startDelay,
                    $endDelay,
                    $downtime['dtp_day_of_month'],
                    $startTimestamp,
                    $endTimestamp,
                    $tomorrow
                );
            } else if (preg_match('/^\d(,\d)*$/', $downtime['dtp_day_of_week']) && $downtime['dtp_month_cycle'] != 'none') {
                $approaching = $this->isSpecificDateDowntime(
                    $startDelay,
                    $endDelay,
                    $downtime['dtp_day_of_week'],
                    $downtime['dtp_month_cycle'],
                    $startTimestamp,
                    $endTimestamp,
                    $tomorrow
                );
            }

            if ($approaching) {
                $approachingDowntimes[] = array(
                    'dt_id' => $downtime['dt_id'],
                    'dt_activate' => $downtime['dt_activate'],
                    'start' => $startTimestamp,
                    'end' => $endTimestamp,
                    'host_id' => $downtime['host_id'],
                    'host_name' => $downtime['host_name'],
                    'service_id' => $downtime['service_id'],
                    'service_description' => $downtime['service_description'],
                    'fixed' => $downtime['dtp_fixed'],
                    'duration' => $downtime['dtp_duration'],
                    'tomorrow' => $tomorrow
                );
            }
        }

        return $approachingDowntimes;
    }

    public function isScheduled($downtime)
    {
        $isScheduled = false;

        $query = 'SELECT internal_id as internal_downtime_id, type as downtime_type, host_id, service_id '
            . 'FROM downtimes '
            . 'WHERE start_time = ' . $downtime['start'] . ' '
            . 'AND end_time = ' . $downtime['end'] . ' '
            . 'AND host_id = ' . $downtime['host_id'] . ' '
            . 'AND comment_data = "[Downtime cycle #' . $downtime['dt_id'] . ']" ';
        $query .= ($downtime['service_id'] != '') ? 'AND service_id = ' . $downtime['service_id'] . ' ' : '';
        $res = $this->dbb->query($query);
        if ($res->numRows()) {
            $isScheduled = true;
        }

        return $isScheduled;
    }
}
