<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus
 *
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 *
 * For information : contact@centreon.com
 */

	if (!isset($oreon))
		exit();
	/**
	 *
	 * Write command in nagios pipe or in centcore pipe.
	 * @param $cmd
	 * @param $poller
	 */
	function write_command($cmd, $poller){
		global $oreon, $key, $pearDB;
		$str = NULL;

		$informations = split(";", $key);
		if ($poller && isPollerLocalhost($pearDB, $poller))
			$str = "echo '[" . time() . "]" . $cmd . "\n' >> " . $oreon->Nagioscfg["command_file"];
		else if (isHostLocalhost($pearDB, $informations[0]))
			$str = "echo '[" . time() . "]" . $cmd . "\n' >> " . $oreon->Nagioscfg["command_file"];
		else
			$str = "echo 'EXTERNALCMD:$poller:[" . time() . "]" . $cmd . "\n' >> " . "@CENTREON_VARLIB@/centcore.cmd";
		return passthru($str);
	}

	/**
	 *
	 * Ack hosts massively
	 * @param $key
	 */
	function massiveHostAck($key){
        global $pearDB, $is_admin, $oreon;

		$actions = false;
        $actions = $oreon->user->access->checkAction("host_acknowledgement");

        $key = urldecode($key);
		$tmp = split(";", $key);
		$host_name = $tmp[0];

		isset($_GET['persistent']) && $_GET['persistent'] == "true" ? $persistent = "1" : $persistent = "0";
		isset($_GET['notify']) && $_GET['notify'] == "true" ? $notify = "1" : $notify = "0";
		isset($_GET['sticky']) && $_GET['sticky'] == "true" ? $sticky = "2" : $sticky = "1";
		isset($_GET['force_check']) && $_GET['force_check'] == "true" ? $force_check = "1" : $force_check = "0";

		if ($actions == true || $is_admin) {
			$host_poller = GetMyHostPoller($pearDB, $host_name);
			$flg = write_command(" ACKNOWLEDGE_HOST_PROBLEM;".$host_name.";".$sticky.";".$notify.";".$persistent.";".$_GET["author"].";".$_GET["comment"], $host_poller);
		}

		print_r($_GET);

		$actions = $oreon->user->access->checkAction("service_acknowledgement");
		if (($actions == true || $is_admin) && isset($_GET['ackhostservice']) && $_GET['ackhostservice'] == "true") {
			$DBRES = $pearDB->query("SELECT host_id FROM `host` WHERE host_name = '".$host_name."' LIMIT 1");
			$row =& $DBRES->fetchRow();
			$svc_tab = array();
			$svc_tab = getMyHostServices($row['host_id']);
			if (count($svc_tab)) {
				foreach ($svc_tab as $key2 => $value) {
					write_command(" ACKNOWLEDGE_SVC_PROBLEM;".$host_name.";".$value.";".$sticky.";".$notify.";".$persistent.";".$_GET["author"].";".$_GET["comment"], $host_poller);
					if ($force_check == 1 && $oreon->user->access->checkAction("service_schedule_forced_check") == true)
				 		write_command(" SCHEDULE_FORCED_SVC_CHECK;".$host_name.";".time(), $host_poller);
				}
			}
		}

		/*
		 * Set param in memory
		 */
		set_user_param($oreon->user->user_id, $pearDB, "ack_sticky", $sticky);
		set_user_param($oreon->user->user_id, $pearDB, "ack_notify", $notify);
		set_user_param($oreon->user->user_id, $pearDB, "ack_persistent", $persistent);
		set_user_param($oreon->user->user_id, $pearDB, "force_check", $force_check);

		return _("Your command has been sent");
	}

    /**
     *
     * Ack services massively
     * @param $key
     */
	function massiveServiceAck($key){
    	global $pearDB, $is_admin, $oreon;

		$actions = false;
		$actions = $oreon->user->access->checkAction("service_acknowledgement");

		$key = urldecode($key);

		$tmp = split(";", $key);

		if (!isset($tmp[0])) {
			throw new Exception('No host found');
		}
		$host_name = $tmp[0];

		if (!isset($tmp[1])) {
			throw new Exception('No service found');
		} else {
			$svc_description = $tmp[1];
		}

		isset($_GET['persistent']) && $_GET['persistent'] == "true" 		? $persistent = "1" : $persistent = "0";
		isset($_GET['notify']) && $_GET['notify'] == "true" 			? $notify = "1" : $notify = "0";
		isset($_GET['sticky']) && $_GET['sticky'] == "true" 			? $sticky = "2" : $sticky = "1";
		isset($_GET['force_check']) && $_GET['force_check'] == "true" 		? $force_check = "1" : $force_check = "0";

		$host_poller = GetMyHostPoller($pearDB, $host_name);

		if ($actions == true || $is_admin) {

			$_GET["comment"] = $_GET["comment"];
			$_GET["comment"] = str_replace('\'', ' ', $_GET["comment"]);

			$flg = write_command(" ACKNOWLEDGE_SVC_PROBLEM;".$host_name.";".$svc_description.";".$sticky.";".$notify.";".$persistent.";".$_GET["author"].";".$_GET["comment"], $host_poller);

			if ($force_check == 1 && $oreon->user->access->checkAction("service_schedule_forced_check") == true) {
				write_command(" SCHEDULE_FORCED_SVC_CHECK;".$host_name.";".$svc_description.";".time(), $host_poller);
			}
			set_user_param($oreon->user->user_id, $pearDB, "ack_sticky", $sticky);
			set_user_param($oreon->user->user_id, $pearDB, "ack_notify", $notify);
			set_user_param($oreon->user->user_id, $pearDB, "ack_persistent", $persistent);
			set_user_param($oreon->user->user_id, $pearDB, "force_check", $force_check);

			return _("Your command has been sent");
		}
		return null;
        }


	/**
	 *
	 * Sets host downtime massively
	 * @param $key
	 */
    function massiveHostDowntime($key) {
    	global $pearDB, $is_admin, $oreon, $centreonGMT;

        $actions = false;
        $actions = $oreon->user->access->checkAction("host_schedule_downtime");

        if ($actions == true || $is_admin) {
        	$key = urldecode($key);

        	$tmp = split(";", $key);
        	if (!isset($tmp[0])) {
				throw new Exception('No host found');
			}
			$host_name = $tmp[0];

			isset($_GET['start']) && $_GET['start'] ? $start = $_GET['start'] : $start = time();
			isset($_GET['end']) && $_GET['end'] ? $end = $_GET['end'] : $end = time();
			isset($_GET['comment']) && $_GET['comment'] ? $comment = str_replace('\'', ' ', $_GET["comment"]) : $comment = "";
			isset($_GET['fixed']) && $_GET['fixed'] == "true" ? $fixed = 1 : $fixed = 0;
			isset($_GET['duration']) && $_GET['duration'] && is_numeric($_GET['duration']) ? $duration = $_GET['duration'] : $duration = 0;

			$res = preg_split("/ /", $start);
			if (count($res) != 2) {
				throw new Exception('Start date format is not valid');
			}
			$res1 = preg_split("/\//", $res[0]);
			$res2 = preg_split("/:/", $res[1]);

			$start_time = mktime($res2[0], $res2[1], "0", $res1[1], $res1[2], $res1[0]);
			$start_time = $centreonGMT->getUTCDate($start_time);

			$res = preg_split("/ /", $end);
        	if (count($res) != 2) {
				throw new Exception('End date format is not valid');
			}
			$res3 = preg_split("/\//", $res[0]);
			$res4 = preg_split("/:/", $res[1]);
			$end_time = mktime($res4[0], $res4[1], "0", $res3[1], $res3[2], $res3[0]);
			$end_time = $centreonGMT->getUTCDate($end_time);
            if (!$duration) {
			    $duration = $end_time - $start_time;
			}
			$hostPoller = GetMyHostPoller($pearDB, $host_name);
			write_command(" SCHEDULE_HOST_DOWNTIME;".$host_name.";".$start_time.";".$end_time.";".$fixed.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n", $hostPoller);

			if (($oreon->user->access->checkAction("service_schedule_downtime") == true)
				&& isset($_GET['downtimehostservice']) && $_GET['downtimehostservice'] == "true") {

				$DBRES = $pearDB->query("SELECT host_id FROM `host` WHERE host_name = '".$host_name."' LIMIT 1");
	            $row = $DBRES->fetchRow();
				$svc_tab = array();
	            $svc_tab = getMyHostServices($row['host_id']);
	            if (count($svc_tab)) {
					foreach ($svc_tab as $key2 => $value) {
	            		write_command(" SCHEDULE_SVC_DOWNTIME;".$host_name.";".$value.";".$start_time.";".$end_time.";".$fixed.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n", $hostPoller);
	                }
				}
			}
        }
        return null;
        }

	/*
	 *  Sets service downtime massively
	 */
	function massiveServiceDowntime($key) {
		global $pearDB, $is_admin, $oreon, $centreonGMT;

        $actions = false;
        $actions = $oreon->user->access->checkAction("service_schedule_downtime");

        if ($actions == true || $is_admin) {
        	$key = urldecode($key);
        	$tmp = split(";", $key);

        	if (!isset($tmp[0])) {
				throw new Exception('No host found');
			}
			$host_name = $tmp[0];

			if (!isset($tmp[1])) {
				throw new Exception('No service found');
        	} else {
				$svc_description = $tmp[1];
			}

			isset($_GET['start']) && $_GET['start'] ? $start = $_GET['start'] : $start = time();
			isset($_GET['end']) && $_GET['end'] ? $end = $_GET['end'] : $end = time();
			isset($_GET['comment']) && $_GET['comment'] ? $comment = str_replace('\'', ' ', $_GET["comment"]) : $comment = "";
			isset($_GET['fixed']) && $_GET['fixed'] == "true" ? $fixed = 1 : $fixed = 0;
			isset($_GET['duration']) && $_GET['duration'] && is_numeric($_GET['duration']) ? $duration = $_GET['duration'] : $duration = 0;

			$res = preg_split("/ /", $start);
        	if (count($res) != 2) {
				throw new Exception('Start date format is not valid');
			}
			$res1 = preg_split("/\//", $res[0]);
			$res2 = preg_split("/:/", $res[1]);
			$start_time = mktime($res2[0], $res2[1], "0", $res1[1], $res1[2], $res1[0], -1);

			$start_time = $centreonGMT->getUTCDate($start_time);
			$res = preg_split("/ /", $end);
        	if (count($res) != 2) {
				throw new Exception('End date format is not valid');
			}
			$res3 = preg_split("/\//", $res[0]);
			$res4 = preg_split("/:/", $res[1]);
			$end_time = mktime($res4[0], $res4[1], "0", $res3[1], $res3[2], $res3[0], -1);
			$end_time = $centreonGMT->getUTCDate($end_time);
			if (!$duration) {
			    $duration = $end_time - $start_time;
			}
			write_command(" SCHEDULE_SVC_DOWNTIME;".urldecode($host_name).";".urldecode($svc_description).";".$start_time.";".$end_time.";".$fixed.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n", GetMyHostPoller($pearDB, urldecode($host_name)));
        }
        return null;
        }
?>
