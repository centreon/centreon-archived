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
require_once realpath(dirname(__FILE__) . "/../../config/centreon.config.php");
require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/include/common/common-Func.php";

/*
 *  This class allows the user to send external commands to Nagios
 */

class CentreonExternalCommand
{
    protected $DB;
    protected $DBC;
    protected $cmdTab;
    protected $pollerTab;
    public $localhostTab = array();
    protected $actions = array();
    protected $GMT;
    protected $obj; // Centreon Obj
    public $debug = 0;

    /*
     *  Constructor
     */

    public function __construct($oreon)
    {
        global $oreon;

        $this->obj = $oreon;
        $this->DB = new CentreonDB();
        $this->DBC = new CentreonDB('centstorage');

        $rq = "SELECT id FROM `nagios_server` WHERE localhost = '1'";
        $DBRES = $this->DB->query($rq);
        while ($row = $DBRES->fetchRow()) {
            $this->localhostTab[$row['id']] = "1";
        }
        $DBRES->free();

        $this->setExternalCommandList();

        /*
         * Init GMT classes
         */
        $this->GMT = new CentreonGMT($this->DB);
        $this->GMT->getMyGMTFromSession(session_id(), $this->DB);
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

        $str_local = "";
        $str_remote = "";
        $return_local = 0;
        $return_remote = 0;

        if (count($this->cmdTab)) {
            foreach ($this->cmdTab as $key => $cmd) {
                $cmd = str_replace("\"", "", $cmd);
                $cmd = str_replace("\n", "<br>", $cmd);
                if (isset($this->localhostTab[$this->pollerTab[$key]])) {
                    $str_local .= "\"[" . time() . "] " . $cmd . "\n\"";
                } else {
                    $str_remote .= "\"EXTERNALCMD:" . $this->pollerTab[$key] . ":[" . time() . "] " . $cmd . "\n\"";
                }
            }
        }

        if ($str_local != "") {
            $str_local = "echo " . $str_local . " >> " . $centreon->Nagioscfg["command_file"];
            if ($this->debug) {
                print "COMMAND BEFORE SEND: $str_local";
            }
            passthru(trim($str_local), $return_local);
        }
        if ($str_remote != "") {
            $str_remote = "echo " . $str_remote . " >> $varlib/centcore.cmd";
            if ($this->debug) {
                print "COMMAND BEFORE SEND: $str_remote";
            }
            passthru($str_remote, $return_remote);
        }
        $this->cmdTab = array();
        $this->pollerTab = array();
        return ($return_local + $return_remote);
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

        $this->actions["service_acknowledgement"] = "";
        $this->actions["service_disacknowledgement"] = "";

        $this->actions["service_schedule_check"][0] = "SCHEDULE_SVC_CHECK";
        $this->actions["service_schedule_check"][1] = "SCHEDULE_FORCED_SVC_CHECK";

        $this->actions["service_schedule_downtime"] = "";

        $this->actions["service_comment"] = "";

        $this->actions["service_event_handler"][0] = "ENABLE_SVC_EVENT_HANDLER";
        $this->actions["service_event_handler"][1] = "DISABLE_SVC_EVENT_HANDLER";

        $this->actions["service_flap_detection"][0] = "ENABLE_SVC_FLAP_DETECTION";
        $this->actions["service_flap_detection"][1] = "DISABLE_SVC_FLAP_DETECTION";

        $this->actions["service_passive_checks"][0] = "ENABLE_PASSIVE_SVC_CHECKS";
        $this->actions["service_passive_checks"][1] = "DISABLE_PASSIVE_SVC_CHECKS";

        $this->actions["service_submit_result"] = "";

        $this->actions["service_obsess"][0] = "START_OBSESSING_OVER_SVC";
        $this->actions["service_obsess"][1] = "STOP_OBSESSING_OVER_SVC";

        # Hosts Actions
        $this->actions["host_checks"][0] = "ENABLE_HOST_CHECK";
        $this->actions["host_checks"][1] = "DISABLE_HOST_CHECK";

        $this->actions["host_passive_checks"][0] = "ENABLE_PASSIVE_HOST_CHECKS";
        $this->actions["host_passive_checks"][1] = "DISABLE_PASSIVE_HOST_CHECKS";

        $this->actions["host_notifications"][0] = "ENABLE_HOST_NOTIFICATIONS";
        $this->actions["host_notifications"][1] = "DISABLE_HOST_NOTIFICATIONS";

        $this->actions["host_acknowledgement"] = "";
        $this->actions["host_disacknowledgement"] = "";

        $this->actions["host_schedule_check"][0] = "SCHEDULE_HOST_SVC_CHECKS";
        $this->actions["host_schedule_check"][1] = "SCHEDULE_FORCED_HOST_SVC_CHECKS";

        $this->actions["host_schedule_downtime"] = "";

        $this->actions["host_comment"] = "";

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
     * @param $pearDB
     * @param $host_name
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
            $DBRESULT = $this->DBC->query("SELECT instance_id FROM hosts WHERE hosts.host_id = '" . 
                CentreonDB::escape($host) . "' AND hosts.enabled = '1'");
        } else {
            $DBRESULT = $this->DBC->query("SELECT instance_id FROM hosts WHERE hosts.name = '" . 
                CentreonDB::escape($host) . "' AND hosts.enabled = '1' LIMIT 1");
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

    /**     * ****************************************************
     * Downtime
     */

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
     * Get date from string
     *
     * date format: m/d/Y H:i
     * @param string $string
     */
    private function getDate($string)
    {
        $res = preg_split("/ /", $string);
        $res3 = preg_split("/\//", $res[0]);
        $res4 = preg_split("/:/", $res[1]);
        $end_time = mktime($res4[0], $res4[1], "0", $res3[0], $res3[1], $res3[2]);
        unset($res);
        return $end_time;
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
            $start_time = $this->GMT->getUTCDateFromString(
                $start,
                $this->GMT->getMyGTMFromUser($centreon->user->get_id())
            );
            $end_time = $this->GMT->getUTCDateFromString(
                $end,
                $this->GMT->getMyGTMFromUser($centreon->user->get_id())
            );
        } else {
            $start_time = $this->GMT->getUTCDateFromString($start, $this->GMT->getUTCLocationHost($host));
            $end_time = $this->GMT->getUTCDateFromString($end, $this->GMT->getUTCLocationHost($host));
        }

        /*
         * Get poller for this host
         */
        $poller_id = $this->getPollerID($host);
        
        /*
         * Send command
         */
        if (!isset($duration)) {
            $duration = $start_time - $end_time;
        }
        $this->setProcessCommand(
            "SCHEDULE_HOST_DOWNTIME;" . getMyHostName($host) . ";" . $start_time . ";" . $end_time .
            ";" . $persistant . ";0;" . $duration . ";" . $centreon->user->get_alias() . ";" . $comment,
            $poller_id
        );
        if ($withServices === true) {
            $this->setProcessCommand(
                "SCHEDULE_HOST_SVC_DOWNTIME;" . getMyHostName($host) . ";" . $start_time . ";" . $end_time .
                ";" . $persistant . ";0;" . $duration . ";" . $centreon->user->get_alias() . ";" . $comment,
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
            $start_time = $this->GMT->getUTCDateFromString(
                $start,
                $this->GMT->getMyGTMFromUser($centreon->user->get_id())
            );
            $end_time = $this->GMT->getUTCDateFromString(
                $end,
                $this->GMT->getMyGTMFromUser($centreon->user->get_id())
            );
        } else {
            $start_time = $this->GMT->getUTCDateFromString($start, $this->GMT->getUTCLocationHost($host));
            $end_time = $this->GMT->getUTCDateFromString($end, $this->GMT->getUTCLocationHost($host));
        }

        /*
         * Get poller for this host
         */
        $poller_id = $this->getPollerID($host);

        /*
         * Send command
         */
        if (!isset($duration)) {
            $duration = $start_time - $end_time;
        }
        $this->setProcessCommand(
            "SCHEDULE_SVC_DOWNTIME;" . getMyHostName($host) . ";" . getMyServiceName($service) . ";" . $start_time .
            ";" . $end_time . ";" . $persistant . ";0;" . $duration . ";" . $centreon->user->get_alias() .
            ";" . $comment,
            $poller_id
        );
        $this->write();
    }
}
