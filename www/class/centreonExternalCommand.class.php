<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

require_once "@CENTREON_ETC@/centreon.conf.php";
//require_once "/etc/centreon/centreon.conf.php";
require_once $centreon_path . "/www/class/centreonDB.class.php";
require_once $centreon_path . "/www/include/common/common-Func.php";

/*
 *  This class allows the user to send external commands to Nagios
 */
class CentreonExternalCommand {
 	var $DB;
 	var $cmd_tab;
 	var $poller_tab;
 	var $localhost_tab = array();
 	var $actions = array();
 	var $GMT;
 	var $obj; // Centreon Obj
 	var $debug = 0;

 	/*
 	 *  Constructor
 	 */
 	function CentreonExternalCommand($oreon) {
 		global $oreon;

 		$this->obj = $oreon;
 		$this->DB = new CentreonDB();

 		$rq = "SELECT id FROM `nagios_server` WHERE localhost = '1'";
 		$DBRES = $this->DB->query($rq);
 		while ($row = $DBRES->fetchRow()) {
 			$this->localhost_tab[$row['id']] = "1";
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
 	public function write() {
 		global $oreon;

 		$varlib = "@CENTREON_VARLIB@";
 		if ($varlib == "" || $varlib == "@CENTREON_VARLIB@") {
 			$varlib = "/var/lib/centreon";
 		}

 		$str_local = "";
 		$str_remote = "";
 		$return_local = 0;
 		$return_remote = 0;

 		if (count($this->cmd_tab)) {
		 	foreach ($this->cmd_tab as $key => $cmd) {
				$cmd = str_replace("\"", "", $cmd);
				$cmd = str_replace("\n", "<br>", $cmd);
		 		if (isset($this->localhost_tab[$this->poller_tab[$key]])) {
					$str_local .= "\"[" . time() . "] " . $cmd . "\n\"";
	 			} else {
	 				$str_remote .= "\"EXTERNALCMD:".$this->poller_tab[$key].":[" . time() . "] " . $cmd . "\n\"";
				}
	 		}
 		}

 		if ($str_local != "") {
 			$str_local = "echo " . $str_local . " >> " . $oreon->Nagioscfg["command_file"];
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
 		return ($return_local + $return_remote);
 	}

 	/*
 	 *  set basic process commands
 	 */
 	public function set_process_command($command, $poller) {
 		if ($this->debug) {
			print "POLLER: $poller<br>";
			print "COMMAND: $command<br>";
 		}

 		$this->cmd_tab[] = $command;
 		$this->poller_tab[] = $poller;
 	}

 	/*
 	 *  set list of external commands
 	 */
 	private function setExternalCommandList() {
 		# Services Actions
		$this->actions["service_checks"][0] = "ENABLE_SVC_CHECK";
		$this->actions["service_checks"][1] = "DISABLE_SVC_CHECK";

		$this->actions["service_notifications"][0] = "ENABLE_SVC_NOTIFICATIONS";
		$this->actions["service_notifications"][1] = "DISABLE_SVC_NOTIFICATIONS";

		$this->actions["service_acknowledgement"] = "";

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

		$this->actions["host_schedule_check"][0] = "SCHEDULE_HOST_SVC_CHECKS";
		$this->actions["host_schedule_check"][1] = "SCHEDULE_FORCED_HOST_SVC_CHECKS";

		$this->actions["host_schedule_downtime"] = "";

		$this->actions["host_comment"] = "";

		$this->actions["host_event_handler"][0] = "ENABLE_HOST_EVENT_HANDLER";
		$this->actions["host_event_handler"][1]	= "DISABLE_HOST_EVENT_HANDLER";

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
	public function getPollerID($host = NULL){
		if (!isset($host)) {
			return 0;
		}

		/*
		 * Check if $host is an id or a name
		 */
		if (preg_match("/^[0-9]*$/", $host)) {
			$DBRESULT = $this->DB->query("SELECT `id` FROM nagios_server, ns_host_relation, host WHERE ns_host_relation.host_host_id = '$host' AND ns_host_relation.nagios_server_id = nagios_server.id LIMIT 1");
		} else {
			$DBRESULT = $this->DB->query("SELECT `id` FROM nagios_server, ns_host_relation, host WHERE host.host_name = '$host' AND host.host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = nagios_server.id LIMIT 1");
		}
		$nagios_server = $DBRESULT->fetchRow();
		if (isset($nagios_server['id'])) {
			return $nagios_server['id'];
		}
		return 0;
	}

 	/**
 	 *
 	 * get list of external commands
 	 */
 	public function getExternalCommandList() {
 		return $this->actions;
 	}

 	/** *****************************************************
 	 * Downtime
 	 */

 	/**
 	 *
 	 * Delete downtimes.
 	 * @param string $type
 	 * @param array $hosts
 	 */
 	public function DeleteDowntime($type, $hosts = array()){
		foreach ($hosts as $key => $value) {
			$res = split(";", $key);
			$poller_id = $this->getPollerID($res[0]);
			$this->set_process_command("DEL_".$type."_DOWNTIME;".$res[1], $poller_id);
		}
		$this->write();
	}

	/**
	 *
	 * Get date from string
	 * @param string $string
	 */
	private function getDate($string) {
		$res = preg_split("/ /", $string);
		$res3 = preg_split("/\//", $res[0]);
		$res4 = preg_split("/:/", $res[1]);
		$end_time = mktime($res4[0], $res4[1], "0", $res3[1], $res3[2], $res3[0]);
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
	public function AddHostDowntime($host, $comment, $start, $end, $persistant, $duration = null)
	{
		global $centreon;

		if (!isset($persistant)) {
			$persistant = 0;
		}

		$start_time = $this->GMT->getUTCDate($this->getDate($start));
		$end_time = $this->GMT->getUTCDate($this->getDate($end));

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
		$this->set_process_command("SCHEDULE_HOST_DOWNTIME;".getMyHostName($host).";".$start_time.";".$end_time.";".$persistant.";0;".$duration.";".$centreon->user->get_alias().";".$comment."\n", $poller_id);
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
	public function AddSvcDowntime($host, $service, $comment, $start, $end, $persistant, $duration = null)
	{
		global $centreon;

		if (!isset($persistant)) {
			$persistant = 0;
		}

		$start_time = $this->GMT->getUTCDate($this->getDate($start));
		$end_time = $this->GMT->getUTCDate($this->getDate($end));

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
		$this->set_process_command("SCHEDULE_SVC_DOWNTIME;".getMyHostName($host).";".getMyServiceName($service).";".$start_time.";".$end_time.";".$persistant.";0;".$duration.";".$centreon->user->get_alias().";".$comment."\n", $poller_id);
		$this->write();
	}

}
?>