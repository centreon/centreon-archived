<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();

	$ndo_base_prefix = "nagios";

	if (isset($_GET["host_name"]) && $_GET["host_name"])
		$host_name = $_GET["host_name"];
	else
		foreach ($_GET["select"] as $key => $value)
			$host_name = $key;

	$tab_status = array();

	if (isset($ndo) && $ndo){
		include_once("./DBndoConnect.php");

		/* start ndo svc info */
		$rq ="SELECT " .
				"nss.current_state," .
				" nss.output as plugin_output," .
				" nss.current_check_attempt as current_attempt," .
				" nss.status_update_time as status_update_time," .
				" unix_timestamp(nss.last_state_change) as last_state_change," .
				" unix_timestamp(nss.last_check) as last_check," .
				" nss.notifications_enabled," .
				" unix_timestamp(nss.next_check) as next_check," .
				" nss.problem_has_been_acknowledged," .
				" nss.passive_checks_enabled," .
				" nss.active_checks_enabled," .
				" nss.event_handler_enabled," .
				" nss.is_flapping," .
				" nss.latency as check_latency," .
				" nss.execution_time as check_execution_time," .
				" nss.flap_detection_enabled," .
				" unix_timestamp(nss.last_notification) as last_notification," .
				" no.name1 as host_name," .
				" no.name2 as service_description" .
				" FROM ".$ndo_base_prefix."_servicestatus nss, ".$ndo_base_prefix."_objects no" .
				" WHERE no.object_id = nss.service_object_id AND no.name1 like '".$host_name."' ";

		$DBRESULT_NDO =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT_NDO))
			print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br />";

		$tab_status_service = array();
		$tab_status_service[0] = "OK";
		$tab_status_service[1] = "WARNING";
		$tab_status_service[2] = "CRITICAL";
		$tab_status_service[3] = "UNKNOWN";
		$tab_status_service[4] = "PENDING";

		while($DBRESULT_NDO->fetchInto($ndo))
		{

			if (!isset($tab_status[$ndo["current_state"]]))
				$tab_status[$tab_status_service[$ndo["current_state"]]] = 0;
			$tab_status[$tab_status_service[$ndo["current_state"]]]++;
		}

		/* end ndo service info */

		/* start ndo host detail */
		$tab_host_status[0] = "UP";
		$tab_host_status[1] = "DOWN";
		$tab_host_status[2] = "UNREACHABLE";


		$rq2 = "SELECT nhs.current_state," .
			" nhs.problem_has_been_acknowledged, " .
			" nhs.passive_checks_enabled," .
			" nhs.active_checks_enabled," .
			" nhs.notifications_enabled," .
			" nhs.state_type," .
			" nhs.execution_time as check_execution_time," .
			" nhs.latency as check_latency," .
			" nhs.perfdata as performance_data," .
			" nhs.current_check_attempt as current_attempt," .
			" nhs.state_type," .
			" nhs.check_type," .
			" unix_timestamp(nhs.last_notification) as last_notification," .
			" unix_timestamp(nhs.next_notification) as next_notification," .
			" nhs.is_flapping," .
			" nhs.flap_detection_enabled," .
			" nhs.event_handler_enabled," .
			" nhs.obsess_over_host,".
			" nhs.current_notification_number," .
			" nhs.percent_state_change," .
			" nhs.scheduled_downtime_depth," .
			" unix_timestamp(nhs.last_state_change) as last_state_change," .
			" nhs.output as plugin_output," .
			" unix_timestamp(nhs.last_check) as last_check," .
			" unix_timestamp(nhs.last_notification) as last_notification," .
			" unix_timestamp(nhs.next_check) as next_check," .
			" nh.address," .
			" no.name1 as host_name" .
			" FROM ".$ndo_base_prefix."_hoststatus nhs, ".$ndo_base_prefix."_objects no, ".$ndo_base_prefix."_hosts nh " .
			" WHERE no.object_id = nhs.host_object_id AND no.name1 like '".$host_name."'";

		$DBRESULT_NDO =& $pearDBndo->query($rq2);
		if (PEAR::isError($DBRESULT_NDO))
			print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br />";
		$DBRESULT_NDO->fetchInto($ndo2);


		$host_status[$host_name] = $ndo2;
		$host_status[$host_name]["current_state"] = $tab_host_status[$ndo2["current_state"]];
		/* end ndo host detail */
	}




	$lcaHost = getLcaHostByName($pearDB);

	isset($lcaHost["LcaHost"][$host_name]) || $oreon->user->admin || !$isRestreint ? $key = true : $key = NULL;
	if ($key == NULL){
		include_once("alt_error.php");
	} else {
		$res =& $pearDB->query("SELECT * FROM host WHERE host_name = '".$host_name."'");
		if (PEAR::isError($res))
			print "Mysql Error : ".$res->getMessage();
		$res->fetchInto($hostDB);
		$current_attempts = getMyHostField($hostDB["host_id"], "host_max_check_attempts");

		$res =& $pearDB->query("SELECT * FROM inventory_index WHERE host_id = '".$hostDB["host_name"]."'");
		if (PEAR::isError($res))
			print "Mysql Error : ".$res->getMessage();
		$res->fetchInto($inventory);

		if ($inventory["type_ressources"] == 0){
			$url_id = "p=7&o=t&host_id=" . $key;
		} else if ($inventory["type_ressources"] != 0 && $inventory["type_ressources"] != NULL){
			$url_id = "p=7&o=o&host_id=" . $key;
		} else
			$url_id = NULL;

		$path = "./include/monitoring/objectDetails/";

		# Smarty template Init
		$tpl = new Smarty();
		$tpl = initSmartyTpl($path, $tpl, "./");

		if (!file_exists($oreon->Nagioscfg["comment_file"]))
			print ("downtime file not found");
		else	{
			$log = fopen($oreon->Nagioscfg["comment_file"], "r");
			$tab_comments_host = array();
			$i = 0;
			while ($str = fgets($log))	{
				$res = preg_split("/;/", $str);
				if (preg_match("/^\[([0-9]*)\] HOST_COMMENT;/", $str, $matches)){
					if (!strcmp($res[2], $host_name)){
						$tab_comments_host[$i] = array();
						$tab_comments_host[$i]["id"] = $res[1];
						$tab_comments_host[$i]["host_name"] = $res[2];
						$tab_comments_host[$i]["time"] = date("d-m-Y G:i:s", $matches[1]);
						$tab_comments_host[$i]["author"] = $res[4];
						$tab_comments_host[$i]["comment"] = $res[5];
						$tab_comments_host[$i]["persistent"] = $res[3];
					}
				}
				$i++;
			}
		}

		$en = array("0" => $lang["no"], "1" => $lang["yes"]);

		$en_acknowledge_text = array("1" => $lang ["m_mon_disack"], "0" => $lang ["m_mon_ack"]);
		$en_acknowledge = array("1" => "0", "0" => "1");

		$en_inv = array("1" => "0", "0" => "1");
		$en_inv_text = array("1" => $lang ["m_mon_disable"], "0" => $lang ["m_mon_enable"]);
		$color_onoff = array("1" => "#00ff00", "0" => "#ff0000");
		$color_onoff_inv = array("0" => "#00ff00", "1" => "#ff0000");
		$en_disable = array("1" => $lang ["m_mon_enabled"], "0" => $lang ["m_mon_disabled"]);

		$img_en = array("0" => "<img src='./img/icones/16x16/element_next.gif' border='0'>", "1" => "<img src='./img/icones/16x16/element_previous.gif' border='0'>");


		$host_status[$host_name]["status_color"] = $oreon->optGen["color_".strtolower($host_status[$host_name]["current_state"])];
		$host_status[$host_name]["last_check"] = date($lang["date_time_format"], $host_status[$host_name]["last_check"]);
		$host_status[$host_name]["next_check"] = $host_status[$host_name]["next_check"] ? date($lang["date_time_format"], $host_status[$host_name]["next_check"]) : "";
		!$host_status[$host_name]["last_notification"] ? $host_status[$host_name]["last_notification"] = "": $host_status[$host_name]["last_notification"] = date($lang["date_time_format"], $host_status[$host_name]["last_notification"]);
		!$host_status[$host_name]["last_state_change"] ? $host_status[$host_name]["duration"] = "" : $host_status[$host_name]["duration"] = Duration::toString(time() - $host_status[$host_name]["last_state_change"]);
		!$host_status[$host_name]["last_state_change"] ? $host_status[$host_name]["last_state_change"] = "": $host_status[$host_name]["last_state_change"] = date($lang["date_time_format"],$host_status[$host_name]["last_state_change"]);
		$host_status[$host_name]["last_update"] = date($lang["date_time_format"], time());

		$tab_status_type = array("1" => "HARD", "0" => "SOFT");
		$host_status[$host_name]["state_type"] = $tab_status_type[$host_status[$host_name]["state_type"]];

		$host_status[$host_name]["is_flapping"] = $en[$host_status[$host_name]["is_flapping"]];

		if (isset($ndo) && $ndo)
		if (isset($tab_host_service[$host_name]) && count($tab_host_service[$host_name]))
			foreach ($tab_host_service[$host_name] as $key_name => $s){
				if (!isset($tab_status[$service_status[$host_name."_".$key_name]["current_state"]]))
					$tab_status[$service_status[$host_name."_".$key_name]["current_state"]] = 0;
				$tab_status[$service_status[$host_name."_".$key_name]["current_state"]]++;
			}
		$status = NULL;
		if (isset($tab_status))
			foreach ($tab_status as $key => $value)
				$status .= "&value[".$key."]=".$value;

		$tpl->assign("lang", $lang);
		$tpl->assign("p", $p);
		$tpl->assign("en", $en);
		$tpl->assign("en_inv", $en_inv);
		$tpl->assign("current_attempts", $current_attempts);
		$tpl->assign("en_inv_text", $en_inv_text);
		$tpl->assign("img_en", $img_en);
		$tpl->assign("color_onoff", $color_onoff);
		$tpl->assign("color_onoff_inv", $color_onoff_inv);
		$tpl->assign("en_disable", $en_disable);
		$tpl->assign("img_en", $img_en);
		$tpl->assign("status", $status);
		$tpl->assign("en_acknowledge_text", $en_acknowledge_text);
		$tpl->assign("en_acknowledge", $en_acknowledge);
		$tpl->assign("lcaTopo", $oreon->user->lcaTopo);
		$tpl->assign("h", $hostDB);
		$tpl->assign("url_id", $url_id);
		$tpl->assign("tab_comments_host", $tab_comments_host);
		$tpl->assign("host_data", $host_status[$host_name]);

		# Ext informations
		//$tpl->assign("nagios_path_img", $oreon->optGen["nagios_path_img"]);
		$tpl->assign("h_ext_notes", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_notes"));
		$tpl->assign("h_ext_notes_url", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_notes_url"));
		$tpl->assign("h_ext_action_url_lang", $lang['h_actionUrl']);
		$tpl->assign("h_ext_action_url", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_action_url"));
		//$tpl->assign("h_ext_icon_image", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_icon_image"));
		$tpl->assign("h_ext_icon_image_alt", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_icon_image_alt"));

		$tpl->display("hostDetails.ihtml");
	}
?>