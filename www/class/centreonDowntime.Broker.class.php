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

require_once _CENTREON_PATH_ . 'www/class/centreonDowntime.class.php';
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

    private $scheduledDowntimes = null;

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

    public function isWeeklyApproachingDowntime($startDelay, $endDelay, $daysOfWeek, $tomorrow)
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
            if ($currentDayOfWeek == $dayOfWeek) {
                $isApproaching = true;
            }
        }

        return $isApproaching;
    }

    public function isMonthlyApproachingDowntime($startDelay, $endDelay, $daysOfMonth, $tomorrow)
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
            if ($currentDayOfMonth == $dayOfMonth) {
                $isApproaching = true;
            }
        }

        return $isApproaching;
    }

    public function isSpecificDateDowntime($startDelay, $endDelay, $dayOfWeek, $cycle, $tomorrow)
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

        if ($currentDay == $cycleDay) {
            $isApproaching = true;
        }

        return $isApproaching;
    }

    private function setTime($hourMinute, $timezone, $tomorrow)
    {
        list($hour, $minute) = explode(':', $hourMinute);
        $currentDate = new DateTime();
        $currentDate->setTimezone($timezone);
        $currentDate->setTime($hour, $minute, '00');
        if ($tomorrow) {
            $currentDate->add(new DateInterval('P1D'));
        }

        return $currentDate;
    }

    private function isTomorrow($downtimeStartTime, $now, $delay)
    {
        $tomorrow = false ;

        # startDelay must be between midnight - delay and midnight - 1 second
        $nowTimestamp = strtotime($now->format('H:i'));
        $midnightMoins1SecondDate = new DateTime('midnight -1seconds');
        $midnightMoins1SecondTimestamp = strtotime($midnightMoins1SecondDate->format('H:i:s'));
        $midnightMoinsDelayDate = new DateTime('midnight -' . $delay . 'seconds');
        $midnightMoinsDelayTimestamp = strtotime($midnightMoinsDelayDate->format('H:i'));

        $downtimeStartTimeTimestamp = strtotime($downtimeStartTime);

        # YYYY-MM-DD 00:00:00
        $midnightDate = new DateTime('midnight');
        # 00:00
        $midnight = $midnightDate->format('H:i');
        $midnightTimestamp = strtotime($midnight);

        # YYYY-MM-DD 00:00:10 (for 600 seconds delay)
        $midnightPlusDelayDate = new DateTime('midnight +' . $delay . 'seconds');
        # 00:10 (for 600 seconds delay)
        $midnightPlusDelay = $midnightPlusDelayDate->format('H:i');
        $midnightPlusDelayTimestamp = strtotime($midnightPlusDelay);

        if ($downtimeStartTimeTimestamp >= $midnightTimestamp &&
            $downtimeStartTimeTimestamp <= $midnightPlusDelayTimestamp &&
            $nowTimestamp <= $midnightMoins1SecondTimestamp &&
            $nowTimestamp >= $midnightMoinsDelayTimestamp) {
            $tomorrow = true;
        }

        return $tomorrow;
    }

    private function isApproachingTime($downtimeStart, $delayStart, $delayEnd)
    {
        $approachingTime = false;
        if ($downtimeStart >= $delayStart && $downtimeStart <= $delayEnd) {
            $approachingTime = true;
        }

        return $approachingTime;
    }

    private function manageWinterToSummerTimestamp($time, $timestamp, $timezone)
    {
        $dstDate = new DateTime('now', $timezone);
        $dstDate->setTimestamp($timestamp);
        $dstHour = $dstDate->format('H');
        $hour = $time->format('H');

        $offset = $dstHour - $hour;
        if ($offset > 0) {
            $time->setTime($hour, '00');
            $timestamp = $time->getTimestamp();
        }

        return $timestamp;
    }

    public function getApproachingDowntimes($delay)
    {
        $approachingDowntimes = array();

        $downtimes = $this->getDowntime();

        $hostObj = new CentreonHost($this->db);
        $gmtObj = new CentreonGMT($this->db);

        $startDelay = new DateTime('now');
        $endDelay = new DateTime('now +' . $delay . 'seconds');

        foreach ($downtimes as $downtime) {

            /* Convert HH::mm::ss to HH:mm */
            $downtime['dtp_start_time'] = substr($downtime['dtp_start_time'], 0, strrpos($downtime['dtp_start_time'], ':'));
            $downtime['dtp_end_time'] = substr($downtime['dtp_end_time'], 0, strrpos($downtime['dtp_end_time'], ':'));

            $currentHostDate = $gmtObj->getHostCurrentDatetime($downtime['host_id']);
            $timezone = $currentHostDate->getTimezone();
            $startDelay->setTimezone($timezone);
            $endDelay->setTimezone($timezone);

            $tomorrow = $this->isTomorrow($downtime['dtp_start_time'], $startDelay, $delay);

            $startTime = $this->setTime($downtime['dtp_start_time'], $timezone, $tomorrow);
            $startTimestamp = $startTime->getTimestamp();

            $endTime = $this->setTime($downtime['dtp_end_time'], $timezone, $tomorrow);
            $endTimestamp = $endTime->getTimestamp();

            # Check if HH:mm time is approaching
            if (!$this->isApproachingTime($startTimestamp, $startDelay->getTimestamp(), $endDelay->getTimestamp())) {
                continue;
            }

            # Check if we jump an hour
            $startTimestamp = $this->manageWinterToSummerTimestamp($startTime, $startTimestamp, $timezone);
            $endTimestamp = $this->manageWinterToSummerTimestamp($endTime, $endTimestamp, $timezone);
            if ($startTimestamp == $endTimestamp) {
                continue;
            }

            $approaching = false;
            if (preg_match('/^\d(,\d)*$/', $downtime['dtp_day_of_week']) && preg_match('/^(none)|(all)$/', $downtime['dtp_month_cycle'])) {
                $approaching = $this->isWeeklyApproachingDowntime(
                    $startDelay,
                    $endDelay,
                    $downtime['dtp_day_of_week'],
                    $tomorrow
                );
            } else if (preg_match('/^\d+(,\d+)*$/', $downtime['dtp_day_of_month'])) {
                $approaching = $this->isMonthlyApproachingDowntime(
                    $startDelay,
                    $endDelay,
                    $downtime['dtp_day_of_month'],
                    $tomorrow
                );
            } else if (preg_match('/^\d(,\d)*$/', $downtime['dtp_day_of_week']) && $downtime['dtp_month_cycle'] != 'none') {
                $approaching = $this->isSpecificDateDowntime(
                    $startDelay,
                    $endDelay,
                    $downtime['dtp_day_of_week'],
                    $downtime['dtp_month_cycle'],
                    $tomorrow
                );
            }

            if ($approaching) {
                $approachingDowntimes[] = array(
                    'dt_id' => $downtime['dt_id'],
                    'dt_activate' => $downtime['dt_activate'],
                    'start_hour' => $downtime['dtp_start_time'],
                    'end_hour' => $downtime['dtp_end_time'],
                    'start_timestamp' => $startTimestamp,
                    'end_timestamp' => $endTimestamp,
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

    public function insertCache($downtime)
    {
        $query = 'INSERT INTO downtime_cache '
            . '(downtime_id, start_timestamp, end_timestamp, '
            . 'start_hour, end_hour, host_id, service_id) '
            . 'VALUES ( '
            . $downtime['dt_id'] . ', '
            . $downtime['start_timestamp'] . ', '
            . $downtime['end_timestamp'] . ', '
            . '"' . $downtime['start_hour'] . '", '
            . '"' . $downtime['end_hour'] . '", '
            . $downtime['host_id'] . ', ';
        $query .= ($downtime['service_id'] != '') ? $downtime['service_id'] . ' ' : 'NULL ';
        $query .= ') ';

        $res = $this->db->query($query);
    }

    public function purgeCache()
    {
        $query = 'DELETE FROM downtime_cache '
            . 'WHERE start_timestamp < ' . time();
        $this->db->query($query);
    }

    public function isScheduled($downtime)
    {
        $isScheduled = false;

        $query = 'SELECT downtime_cache_id '
            . 'FROM downtime_cache '
            . 'WHERE downtime_id = ' . $downtime['dt_id'] . ' '
            . 'AND start_timestamp = ' . $downtime['start_timestamp'] . ' '
            . 'AND end_timestamp = ' . $downtime['end_timestamp'] . ' '
            . 'AND host_id = ' . $downtime['host_id'] . ' ';
        $query .= ($downtime['service_id'] != '') ? 'AND service_id = ' . $downtime['service_id'] . ' ' : 'AND service_id IS NULL';

        $res = $this->db->query($query);
        if ($res->numRows()) {
            $isScheduled = true;
        }

        return $isScheduled;
    }

    /**
     * Send external command to nagios or centcore
     *
     * @param int $host_id The host id for command
     * @param string $cmd The command to send
     * @return The command return code
     */
    public function setCommand($host_id, $cmd)
    {
        static $cmdData = null;
        static $remoteCommands = array();
        static $localCommands = array();

        if (is_null($cmdData)) {
            $cmdData = array();
            $query = "SELECT ns.localhost, ns.id, cn.command_file, host_host_id
                                FROM cfg_nagios cn, nagios_server ns, ns_host_relation nsh
                            WHERE cn.nagios_server_id = ns.id
                            AND nsh.nagios_server_id = ns.id
                            AND cn.nagios_activate = '1'
                            AND ns.ns_activate = '1'";
            $res = $this->db->query($query);
            while ($row = $res->fetchRow()) {
                $hid = $row['host_host_id'];
                $cmdData[$hid] = array(
                    'localhost' => $row['localhost'],
                    'command_file' => $row['command_file'],
                    'id' => $row['id']
                );
            }
        }

        if (!isset($cmdData[$host_id])) {
            return;
        }

        if ($cmdData[$host_id]['localhost'] == 1) {
            $this->localCommands[] = $cmd;
            $this->localCmdFile = $cmdData[$host_id]['command_file'];
        } else {
            $this->remoteCommands[] = 'EXTERNALCMD:' . $cmdData[$host_id]['id']  . ':' . $cmd;
        }
    }

    /**
     * Send all commands
     */
    public function sendCommands()
    {
        /* send local commands */
        $localCommands = implode(PHP_EOL, $this->localCommands);
        if ($localCommands && $this->localCmdFile) {
            file_put_contents($this->localCmdFile, $localCommands, FILE_APPEND);
        }

        /* send remote commands */
        $remoteCommands = implode(PHP_EOL, $this->remoteCommands);
        if ($remoteCommands) {
            file_put_contents($this->remoteCmdFile, $remoteCommands, FILE_APPEND);
        }
    }
}
