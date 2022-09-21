<?php
/*
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

// file centreon.config.php may not exist in test environment
$configFile = realpath(dirname(__FILE__) . "/../../config/centreon.config.php");
if ($configFile !== false) {
    require_once $configFile;
}

require_once __DIR__ . '/centreonDB.class.php';
require_once realpath(dirname(__FILE__) . "/centreonDBInstance.class.php");
require_once __DIR__ . '/../include/common/common-Func.php';

/*
 *  This class allows the user to send external commands to Nagios
 */

class CentreonExternalCommand
{
    protected $DB;
    protected $DBC;
    protected $cmdTab = array();
    protected $pollerTab;
    public $localhostTab = array();
    protected $actions = array();
    protected $GMT;
    public $debug = 0;
    protected $userAlias;
    protected $userId;

    /**
     * CentreonExternalCommand constructor.
     */
    public function __construct()
    {
        global $centreon;

        $rq = "SELECT id FROM `nagios_server` WHERE localhost = '1'";
        $DBRES = CentreonDBInstance::getConfInstance()->query($rq);
        while ($row = $DBRES->fetchRow()) {
            $this->localhostTab[$row['id']] = "1";
        }
        $DBRES->closeCursor();

        $this->setExternalCommandList();

        /*
         * Init GMT classes
         */
        $this->GMT = new CentreonGMT();
        $this->GMT->getMyGMTFromSession(session_id(), CentreonDBInstance::getConfInstance());

        if (!is_null($centreon)) {
            $this->userId = $centreon->user->get_id();
            $this->userAlias = $centreon->user->get_alias();
        }
    }

    /**
     * @param $newUserId
     */
    public function setUserId($newUserId)
    {
        $this->userId = $newUserId;
    }

    /**
     * @param $newUserAlias
     */
    public function setUserAlias($newUserAlias)
    {
        $this->userAlias = $newUserAlias;
    }

    /**
     *
     * Write command in Nagios or Centcore Pipe.
     */
    public function write()
    {
        global $centreon;

        if (!defined('_CENTREON_VARLIB_')) {
            $varlib = "/var/lib/centreon";
        } else {
            $varlib = _CENTREON_VARLIB_;
        }

        $str_remote = "";
        $return_remote = 0;

        foreach ($this->cmdTab as $key => $cmd) {
            $cmd = str_replace("\"", "", $cmd);
            $cmd = str_replace("\n", "<br>", $cmd);
            $cmd = "[" . time() . "] " . $cmd . "\n";
            $str_remote .= "EXTERNALCMD:" . $this->pollerTab[$key] . ":" . $cmd;
        }

        if ($str_remote != "") {
            if ($this->debug) {
                print "COMMAND BEFORE SEND: $str_remote";
            }
            $result = file_put_contents($varlib . '/centcore/' . microtime(true) . '-externalcommand.cmd', $str_remote, FILE_APPEND);
            $return_remote = ($result !== false) ? 0 : 1;
        }

        $this->cmdTab = array();
        $this->pollerTab = array();

        return $return_remote;
    }

    /*
     *  set basic process commands
     */
    public function setProcessCommand($command, $poller)
    {
        if ($this->debug) {
            print "POLLER: $poller<br>";
            print "COMMAND: $command<br>";
        }

        $this->cmdTab[] = $command;
        $this->pollerTab[] = $poller;
    }

    /*
     *  set list of external commands
     */

    private function setExternalCommandList()
    {
        # Services Actions
        $this->actions["service_checks"][0] = "ENABLE_SVC_CHECK";
        $this->actions["service_checks"][1] = "DISABLE_SVC_CHECK";

        $this->actions["service_notifications"][0] = "ENABLE_SVC_NOTIFICATIONS";
        $this->actions["service_notifications"][1] = "DISABLE_SVC_NOTIFICATIONS";

        $this->actions["service_acknowledgement"][0] = "ACKNOWLEDGE_SVC_PROBLEM";
        $this->actions["service_disacknowledgement"][0] = "REMOVE_SVC_ACKNOWLEDGEMENT";

        $this->actions["service_schedule_check"][0] = "SCHEDULE_SVC_CHECK";
        $this->actions["service_schedule_check"][1] = "SCHEDULE_FORCED_SVC_CHECK";
        $this->actions["service_schedule_forced_check"][0] = "SCHEDULE_FORCED_SVC_CHECK";

        $this->actions["service_schedule_downtime"][0] = "SCHEDULE_SVC_DOWNTIME";

        $this->actions["service_comment"][0] = "ADD_SVC_COMMENT";

        $this->actions["service_event_handler"][0] = "ENABLE_SVC_EVENT_HANDLER";
        $this->actions["service_event_handler"][1] = "DISABLE_SVC_EVENT_HANDLER";

        $this->actions["service_flap_detection"][0] = "ENABLE_SVC_FLAP_DETECTION";
        $this->actions["service_flap_detection"][1] = "DISABLE_SVC_FLAP_DETECTION";

        $this->actions["service_passive_checks"][0] = "ENABLE_PASSIVE_SVC_CHECKS";
        $this->actions["service_passive_checks"][1] = "DISABLE_PASSIVE_SVC_CHECKS";

        $this->actions["service_submit_result"][0] = "PROCESS_SERVICE_CHECK_RESULT";

        $this->actions["service_obsess"][0] = "START_OBSESSING_OVER_SVC";
        $this->actions["service_obsess"][1] = "STOP_OBSESSING_OVER_SVC";

        # Hosts Actions
        $this->actions["host_checks"][0] = "ENABLE_HOST_CHECK";
        $this->actions["host_checks"][1] = "DISABLE_HOST_CHECK";

        $this->actions["host_passive_checks"][0] = "ENABLE_PASSIVE_HOST_CHECKS";
        $this->actions["host_passive_checks"][1] = "DISABLE_PASSIVE_HOST_CHECKS";

        $this->actions["host_notifications"][0] = "ENABLE_HOST_NOTIFICATIONS";
        $this->actions["host_notifications"][1] = "DISABLE_HOST_NOTIFICATIONS";

        $this->actions["host_acknowledgement"][0] = "ACKNOWLEDGE_HOST_PROBLEM";
        $this->actions["host_disacknowledgement"][0] = "REMOVE_HOST_ACKNOWLEDGEMENT";

        $this->actions["host_schedule_check"][0] = "SCHEDULE_HOST_SVC_CHECKS";
        $this->actions["host_schedule_check"][1] = "SCHEDULE_FORCED_HOST_SVC_CHECKS";
        $this->actions["host_schedule_forced_check"][0] = "SCHEDULE_FORCED_HOST_SVC_CHECKS";

        $this->actions["host_schedule_downtime"][0] = "SCHEDULE_HOST_DOWNTIME";

        $this->actions["host_comment"][0] = "ADD_HOST_COMMENT";

        $this->actions["host_event_handler"][0] = "ENABLE_HOST_EVENT_HANDLER";
        $this->actions["host_event_handler"][1] = "DISABLE_HOST_EVENT_HANDLER";

        $this->actions["host_flap_detection"][0] = "ENABLE_HOST_FLAP_DETECTION";
        $this->actions["host_flap_detection"][1] = "DISABLE_HOST_FLAP_DETECTION";

        $this->actions["host_checks_for_services"][0] = "ENABLE_HOST_SVC_CHECKS";
        $this->actions["host_checks_for_services"][1] = "DISABLE_HOST_SVC_CHECKS";

        $this->actions["host_notifications_for_services"][0] = "ENABLE_HOST_SVC_NOTIFICATIONS";
        $this->actions["host_notifications_for_services"][1] = "DISABLE_HOST_SVC_NOTIFICATIONS";

        $this->actions["host_obsess"][0] = "START_OBSESSING_OVER_HOST";
        $this->actions["host_obsess"][1] = "STOP_OBSESSING_OVER_HOST";

        # Global Nagios External Commands
        $this->actions["global_shutdown"][0] = "SHUTDOWN_PROGRAM";
        $this->actions["global_shutdown"][1] = "SHUTDOWN_PROGRAM";

        $this->actions["global_restart"][0] = "RESTART_PROGRAM";
        $this->actions["global_restart"][1] = "RESTART_PROGRAM";

        $this->actions["global_notifications"][0] = "ENABLE_NOTIFICATIONS";
        $this->actions["global_notifications"][1] = "DISABLE_NOTIFICATIONS";

        $this->actions["global_service_checks"][0] = "START_EXECUTING_SVC_CHECKS";
        $this->actions["global_service_checks"][1] = "STOP_EXECUTING_SVC_CHECKS";

        $this->actions["global_service_passive_checks"][0] = "START_ACCEPTING_PASSIVE_SVC_CHECKS";
        $this->actions["global_service_passive_checks"][1] = "STOP_ACCEPTING_PASSIVE_SVC_CHECKS";

        $this->actions["global_host_checks"][0] = "START_EXECUTING_HOST_CHECKS";
        $this->actions["global_host_checks"][1] = "STOP_EXECUTING_HOST_CHECKS";

        $this->actions["global_host_passive_checks"][0] = "START_ACCEPTING_PASSIVE_HOST_CHECKS";
        $this->actions["global_host_passive_checks"][1] = "STOP_ACCEPTING_PASSIVE_HOST_CHECKS";

        $this->actions["global_event_handler"][0] = "ENABLE_EVENT_HANDLERS";
        $this->actions["global_event_handler"][1] = "DISABLE_EVENT_HANDLERS";

        $this->actions["global_flap_detection"][0] = "ENABLE_FLAP_DETECTION";
        $this->actions["global_flap_detection"][1] = "DISABLE_FLAP_DETECTION";

        $this->actions["global_service_obsess"][0] = "START_OBSESSING_OVER_SVC_CHECKS";
        $this->actions["global_service_obsess"][1] = "STOP_OBSESSING_OVER_SVC_CHECKS";

        $this->actions["global_host_obsess"][0] = "START_OBSESSING_OVER_HOST_CHECKS";
        $this->actions["global_host_obsess"][1] = "STOP_OBSESSING_OVER_HOST_CHECKS";

        $this->actions["global_perf_data"][0] = "ENABLE_PERFORMANCE_DATA";
        $this->actions["global_perf_data"][1] = "DISABLE_PERFORMANCE_DATA";
    }

    /**
     *
     * Get poller id where the host is hosted
     * @param null $host
     * @return int
     * @internal param $pearDB
     * @internal param $host_name
     */
    public function getPollerID($host = null)
    {
        if (!isset($host)) {
            return 0;
        }

        /*
         * Check if $host is an id or a name
         */
        if (preg_match("/^[0-9]*$/", $host)) {
            $query = "SELECT instance_id FROM hosts " .
                "WHERE hosts.host_id = '" . CentreonDB::escape($host) . "' AND hosts.enabled = '1'";
            $DBRESULT = CentreonDBInstance::getMonInstance()->query($query);
        } else {
            $query = "SELECT instance_id FROM hosts " .
                "WHERE hosts.name = '" . CentreonDB::escape($host) . "' AND hosts.enabled = '1' LIMIT 1";
            $DBRESULT = CentreonDBInstance::getMonInstance()->query($query);
        }
        $row = $DBRESULT->fetchRow();
        if (isset($row['instance_id'])) {
            return $row['instance_id'];
        }
        return 0;
    }

    /**
     *
     * get list of external commands
     */
    public function getExternalCommandList()
    {
        return $this->actions;
    }

    /****************
     * Schedule check
     ***************/

    /**
     * @param $hostName
     */
    public function scheduleForcedCheckHost($hostName)
    {
        $pollerId = $this->getPollerID($hostName);

        $this->setProcessCommand(
            "SCHEDULE_FORCED_HOST_CHECK;" . $hostName . ";" . time(),
            $pollerId
        );

        $this->write();
    }

    /**
     * @param $hostName
     * @param $serviceDescription
     */
    public function scheduleForcedCheckService($hostName, $serviceDescription)
    {
        $pollerId = $this->getPollerID($hostName);

        $this->setProcessCommand(
            "SCHEDULE_FORCED_SVC_CHECK;" . $hostName . ";" . $serviceDescription . ";" . time(),
            $pollerId
        );

        $this->write();
    }

    /*****************
     * Acknowledgement
     ****************/

    /**
     * @param $hostName
     * @param $serviceDescription
     * @param $sticky
     * @param $notify
     * @param $persistent
     * @param $author
     * @param $comment
     */
    public function acknowledgeHost(
        $hostName,
        $sticky,
        $notify,
        $persistent,
        $author,
        $comment
    ) {
        $pollerId = $this->getPollerID($hostName);

        $this->setProcessCommand(
            "ACKNOWLEDGE_HOST_PROBLEM;" . $hostName . ";" .
            $sticky . ";" . $notify . ";" . $persistent . ";" . $author . ";" . $comment,
            $pollerId
        );

        $this->write();
    }

    /**
     * @param $hostName
     * @param $serviceDescription
     * @param $sticky
     * @param $notify
     * @param $persistent
     * @param $author
     * @param $comment
     */
    public function acknowledgeService(
        $hostName,
        $serviceDescription,
        $sticky,
        $notify,
        $persistent,
        $author,
        $comment
    ) {
        $pollerId = $this->getPollerID($hostName);

        $this->setProcessCommand(
            "ACKNOWLEDGE_SVC_PROBLEM;" . $hostName . ";" . $serviceDescription . ";" .
            $sticky . ";" . $notify . ";" . $persistent . ";" . $author . ";" . $comment,
            $pollerId
        );

        $this->write();
    }

    /**
     *
     * Delete acknowledgement.
     * @param string $type (HOST/SVC)
     * @param array $hosts
     */
    public function deleteAcknowledgement($type, $hosts = array())
    {
        foreach (array_keys($hosts) as $name) {
            $res = preg_split("/\;/", $name);
            $oName = $res[0];
            $pollerId = $this->getPollerID($oName);
            if ($type === 'SVC') {
                $oName .= ';' . $res[1];
            }
            $this->setProcessCommand("REMOVE_" . $type . "_ACKNOWLEDGEMENT;" . $oName, $pollerId);
        }
        $this->write();
    }


    /************
     * Downtime
     ***********/

    private function getDowntimeTimestampFromDate($date = 'now', $timezone = '', $start = true)
    {
        $inputDate = new \DateTime($date . ' GMT');
        $dateTime = new \DateTime($date, new \DateTimeZone($timezone));

        // Winter to summer dst
        $dateTime2 = clone $dateTime;
        $dateTime2->setTimestamp($dateTime2->getTimestamp());

        if ($dateTime2->format("H") != $inputDate->format("H")) {
            $hour = $inputDate->format('H');
            $dateTime->setTime($hour, '00');
            return $dateTime->getTimestamp();
        }

        // Summer to winter dst
        $dateTime3 = clone $dateTime;
        $dateTime3->sub(new \DateInterval('PT1H'));
        if ($dateTime3->format('H:m') === $dateTime->format('H:m')) {
            if ($start) {
                return $dateTime->getTimestamp() - 3600;
            } else {
                return $dateTime->getTimestamp();
            }
        }

        $dateTime4 = clone $dateTime;
        $dateTime4->add(new \DateInterval('PT1H'));
        if ($dateTime4->format('H:m') === $dateTime->format('H:m')) {
            if ($start) {
                return $dateTime->getTimestamp();
            } else {
                return $dateTime->getTimestamp() + 3600;
            }
        }

        return $dateTime->getTimestamp();
    }

    /**
     *
     * Delete downtimes.
     * @param string $type
     * @param array $hosts
     */
    public function deleteDowntime($type, $hosts = array())
    {
        foreach ($hosts as $key => $value) {
            $res = preg_split("/\;/", $key);
            $poller_id = $this->getPollerID($res[0]);
            $this->setProcessCommand("DEL_" . $type . "_DOWNTIME;" . $res[1], $poller_id);
        }
        $this->write();
    }

    /**
     *
     * Add a host downtime
     * @param string $host
     * @param string $comment
     * @param string $start
     * @param string $end
     * @param int $persistant
     */
    public function addHostDowntime(
        $host,
        $comment,
        $start,
        $end,
        $persistant,
        $duration = null,
        $withServices = false,
        $hostOrCentreonTime = "0"
    ) {
        global $centreon;

        if (is_null($centreon)) {
            global $oreon;
            $centreon = $oreon;
        }

        if (!isset($persistant) || !in_array($persistant, array('0', '1'))) {
            $persistant = '0';
        }

        if ($hostOrCentreonTime == "0") {
            $timezoneId = $this->GMT->getMyGTMFromUser($this->userId);
        } else {
            $timezoneId = $this->GMT->getUTCLocationHost($host);
        }
        $timezone = $this->GMT->getActiveTimezone($timezoneId);
        $start_time = $this->getDowntimeTimestampFromDate($start, $timezone, true);
        $end_time = $this->getDowntimeTimestampFromDate($end, $timezone, false);

        if ($end_time == $start_time) {
            return;
        }

        /*
         * Get poller for this host
         */
        $poller_id = $this->getPollerID($host);

        /*
         * Send command
         */
        if (!isset($duration)) {
            $duration = $end_time - $start_time;
        }
        $finalHostName = '';
        if (!is_numeric($host)) {
            $finalHostName .= $host;
        } else {
            $finalHostName .= getMyHostName($host);
        }
        $this->setProcessCommand(
            "SCHEDULE_HOST_DOWNTIME;" . $finalHostName . ";" . $start_time . ";" . $end_time .
            ";" . $persistant . ";0;" . $duration . ";" . $this->userAlias . ";" . $comment,
            $poller_id
        );
        if ($withServices === true) {
            $this->setProcessCommand(
                "SCHEDULE_HOST_SVC_DOWNTIME;" . $finalHostName . ";" . $start_time . ";" . $end_time .
                ";" . $persistant . ";0;" . $duration . ";" . $this->userAlias . ";" . $comment,
                $poller_id
            );
        }
        $this->write();
    }

    /**
     *
     * Add Service Downtime
     * @param string $host
     * @param string $service
     * @param string $comment
     * @param string $start
     * @param string $end
     * @param int $persistant
     */
    public function addSvcDowntime(
        $host,
        $service,
        $comment,
        $start,
        $end,
        $persistant,
        $duration = null,
        $hostOrCentreonTime = "0"
    ) {
        global $centreon;

        if (is_null($centreon)) {
            global $oreon;
            $centreon = $oreon;
        }


        if (!isset($persistant) || !in_array($persistant, array('0', '1'))) {
            $persistant = '0';
        }

        if ($hostOrCentreonTime == "0") {
            $timezoneId = $this->GMT->getMyGTMFromUser($this->userId);
        } else {
            $timezoneId = $this->GMT->getUTCLocationHost($host);
        }

        $timezone = $this->GMT->getActiveTimezone($timezoneId);
        $start_time = $this->getDowntimeTimestampFromDate($start, $timezone, true);
        $end_time = $this->getDowntimeTimestampFromDate($end, $timezone, false);

        if ($end_time == $start_time) {
            return;
        }

        /*
         * Get poller for this host
         */
        $poller_id = $this->getPollerID($host);

        /*
         * Send command
         */
        if (!isset($duration)) {
            $duration = $end_time - $start_time;
        }
        $finalHostName = '';
        if (!is_numeric($host)) {
            $finalHostName .= $host;
        } else {
            $finalHostName .= getMyHostName($host);
        }
        $finalServiceName = '';
        if (!is_numeric($service)) {
            $finalServiceName .= $service;
        } else {
            $finalServiceName .= getMyServiceName($service);
        }
        $this->setProcessCommand(
            "SCHEDULE_SVC_DOWNTIME;" . $finalHostName . ";" . $finalServiceName . ";" . $start_time .
            ";" . $end_time . ";" . $persistant . ";0;" . $duration . ";" . $this->userAlias .
            ";" . $comment,
            $poller_id
        );
        $this->write();
    }
}
